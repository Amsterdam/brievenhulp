<?php
/*
 *  Copyright (C) 2016 X Gemeente
 *                     X Amsterdam
 *                     X Onderzoek, Informatie en Statistiek
 *
 *  This Source Code Form is subject to the terms of the Mozilla Public
 *  License, v. 2.0. If a copy of the MPL was not distributed with this
 *  file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace GemeenteAmsterdam\BrievenhulpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener;
use GemeenteAmsterdam\BrievenhulpBundle\Service\PoolHulpverleners;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerToewijzing;

class AssignHulpverlenersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('brievenhulp:assign-hulpverlener');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $hulpvraagRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpvraagRepository */
        $hulpvraagRepository = $this->getContainer()->get('gemeente_amsterdam_brievenhulp.repositories.hulpvraag');
        /* @var $hulpverlenerRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerRepository */
        $hulpverlenerRepository = $this->getContainer()->get('gemeente_amsterdam_brievenhulp.repositories.hulpverlener');

        $days = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
        $dayBreak = 1200;

        $fnOutput = function ($verbosity, $msg, $data = null) use ($output) {
            $data = (($data === null) ? [] : ((is_array($data) === true) ? $data : ['data' => $data]));
            $output->writeln($msg . ' ' . json_encode($data), $verbosity);
        };

        $hulpverleners = $hulpverlenerRepository->findAll();
        $pool = new PoolHulpverleners($hulpverleners);
        $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Aantal hulpverleners in pool', ['count' => count($pool)]);

        $hulpvragen = $hulpvraagRepository->findHulpvragenWithStatusNietToegewezen();
        $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Aantal hulpvragen zonder hulpverlener', ['count' => count($hulpvragen)]);

        foreach ($hulpvragen as $hulpvraag) {
            $fnOutput(OutputInterface::VERBOSITY_NORMAL, 'Hulpvraag zonder hulpverlener gevonden', ['hulpvraagUuid' => $hulpvraag->getUuid()]);

            // reset the pool
            $pool->reset();

            // filter
            $pool->filterBeschikbaarheid($days[date('w')] . (intval(date('Hi')) < $dayBreak ? '1' : '2') );
            $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Aantal hulpverleners beschikbaar', ['dag' => $days[date('w')], 'count' => count($pool)]);

            // set "load"
            $res = $hulpverlenerRepository->getAantalToegewezenHulpvragenPerHulpverlenerWithMinStatus(Hulpvraag::STATUS_TOEGEWEZEN);
            $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Actuele load informatie opvragen', ['aantalResultaten' => count($res)]);
            foreach ($res as $row) {
                $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Load instellen', ['hulpverlenerId' => $row['hulpverlener']->getId(), 'hulpverlenerNaam' => $row['hulpverlener']->getNaam(), 'load' => $row['load']]);
                $pool->populateLoadInformation($row['hulpverlener'], $row['load']);
            }

            // select lowest
            $lowestLoad = $pool->selectLowestLoad();
            $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Laagste load binnen het de pool', ['load' => $lowestLoad]);

            // filter
            $pool->filterForLoad($lowestLoad);
            $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Aantal hulpverleners met deze load', ['load' => $lowestLoad, 'count' => count($pool)]);

            // handle situation no body is available
            if (count($pool) === 0) {
                if (null === $hulpvraag->getGeenHulpverlenerBeschikbaarReminder()) {
                    // sent mail
                    $time = new \DateTime();
                    $hulpvraag->setGeenHulpverlenerBeschikbaarReminder($time);
                    $this->getContainer()->get('doctrine')->getManager()->flush();
                    $context = ['hulpvraag' => $hulpvraag];
                    $message = \Swift_Message::newInstance();
                    $message->setTo($this->getContainer()->getParameter('mail_cc'));
                    $message->setFrom($this->getContainer()->getParameter('mail_from'));
                    $message->setSubject($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:noCaregiverAvailable.subject.twig', $context));
                    $message->setBody($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:noCaregiverAvailable.html.twig', $context), 'text/html');
                    $this->getContainer()->get('mailer')->send($message);
                }
                $output->writeln(OutputInterface::VERBOSITY_DEBUG, 'Geen hulpverleners in pool beschikbaar. Stop behandeling van deze hulpvraag.');
                continue;
            }

            // get random
            $hulpverlener = $pool->getRandom();
            $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Random hulpverlener geselecteerd', ['hulpverlenerId' => $hulpverlener->getId(), 'hulpverlenerNaam' => $hulpverlener->getNaam()]);

            // assign
            $hulpvraag->setToegewezenHulpverlener($hulpverlener);
            $hulpvraag->setStatus(Hulpvraag::STATUS_TOEGEWEZEN);
            $hulpvraag->setStartDatumTijd(new \DateTime());
            $this->getContainer()->get('doctrine')->getManager()->flush();

            // log
            $hulpverlenerToewijzing = new HulpverlenerToewijzing();
            $hulpverlenerToewijzing->setDatumtijd(new \DateTime());
            $hulpverlenerToewijzing->setHulpverlener($hulpverlener);
            $hulpverlenerToewijzing->setHulpvraag($hulpvraag);
            $hulpverlenerToewijzing->setInfo('Automatische eerste toewijzing');
            $this->getContainer()->get('doctrine')->getManager()->persist($hulpverlenerToewijzing);

            // sent mail
            $now = time();
            $context = [
                'hulpverlener' => $hulpverlener,
                'hulpvraag' => $hulpvraag,
                'sentTime' => $now,
                'hash' => sha1($hulpvraag->getUuid() . $hulpvraag->getSecret() . $hulpverlener->getId() . $hulpverlener->getSecret() . $now)
            ];
            $message = \Swift_Message::newInstance();
            $message->setTo($hulpverlener->getEmail(), $hulpverlener->getNaam());
            $message->setFrom($this->getContainer()->getParameter('mail_from'));
            $message->setSubject($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:submitRequest.subject.twig', $context));
            $message->setBody($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:submitRequest.html.twig', $context), 'text/html');
            $message->addPart($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:submitRequest.txt.twig', $context), 'text/plain');
            $this->getContainer()->get('mailer')->send($message);

            // sent sms
            if ($this->getContainer()->getParameter('messagebird_enable') === true
                && $hulpverlener->getUpdatesPerSms() === true
                && $hulpverlener->getTelefoon() !== ''
                && $hulpverlener->getTelefoon() !== null) {
                $sms = new \MessageBird\Objects\Message();
                $sms->originator = $this->getContainer()->getParameter('messagebird_sms_originator');
                $sms->recipients = array($hulpverlener->getTelefoon());
                $sms->body = $this->renderView('GemeenteAmsterdamBrievenhulpBundle:sms:submitRequest.txt.twig', $context);
                $this->getContainer()->get('messagebird')->messages->create($sms);
            }

            $this->getContainer()->get('doctrine')->getManager()->flush();
            $fnOutput(OutputInterface::VERBOSITY_NORMAL, 'Hulpvraag toegewezen', ['hulpvraagUuid' =>  $hulpvraag->getUuid(), 'hulpverlenerId' => $hulpverlener->getId(), 'hulpverlenerNaam' => $hulpverlener->getNaam()]);
        }
    }

    /**
     * See default Symfony Controller
     * @param string $view
     * @param array $parameters
     * @throws \LogicException
     * @license MIT
     * @copyright Fabien Potencier <fabien@symfony.com>
     */
    protected function renderView($view, array $parameters = array())
    {
        if ($this->getContainer()->has('templating')) {
            return $this->getContainer()->get('templating')->render($view, $parameters);
        }

        if (!$this->getContainer()->has('twig')) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available.');
        }

        return $this->getContainer()->get('twig')->render($view, $parameters);
    }
}