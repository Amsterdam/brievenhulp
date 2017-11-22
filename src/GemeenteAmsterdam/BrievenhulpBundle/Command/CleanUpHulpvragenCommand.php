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
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag;
use Symfony\Component\Filesystem\Filesystem;

class CleanUpHulpvragenCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('brievenhulp:cleanup-hulpvragen');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $hulpvraagRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpvraagRepository */
        $hulpvraagRepository = $this->getContainer()->get('gemeente_amsterdam_brievenhulp.repositories.hulpvraag');
        $fs = new Filesystem();

        $fnOutput = function ($verbosity, $msg, $data = null) use ($output) {
            $data = (($data === null) ? [] : ((is_array($data) === true) ? $data : ['data' => $data]));
            $output->writeln($msg . ' ' . json_encode($data), $verbosity);
        };

        // get hulpvragen
        $hulpvragen = $this->getContainer()->get('gemeente_amsterdam_brievenhulp.repositories.hulpvraag')->findHulpvragenForCleanUp($this->getContainer()->getParameter('retention_policy'));

        $basePath = $this->getContainer()->getParameter('kernel.data_dir') . DIRECTORY_SEPARATOR;

        foreach ($hulpvragen as $hulpvraag) {
            /* @var $hulpvraag \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag */
            try {
                $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'Start clean up', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'status' => $hulpvraag->getStatus(), 'inkomstDatumtijd' => $hulpvraag->getInkomstDatumTijd()->format('c')]);

                // remove file
                $bijlage = $basePath . $hulpvraag->getBestandsnaam();
                if ($fs->exists($bijlage) === true && $hulpvraag->getBestandsnaam() !== null && $hulpvraag->getBestandsnaam() !== '') {
                    $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'File exists, remove', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'file' => $bijlage]);
                    $fs->remove($bijlage);
                    $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'File removed', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'file' => $bijlage]);
                } else {
                    $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'No file exists', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'file' => $bijlage]);
                }

                // remove thumbnails
                $sizes = ['thumb-small', 'thumb-large', 'square'];
                foreach ($sizes as $size) {
                    $dotPosition = strrpos($hulpvraag->getBestandsnaam(), '.');
                    $fileName = substr($hulpvraag->getBestandsnaam(), 0, $dotPosition) . '_' . $size . substr($hulpvraag->getBestandsnaam(), $dotPosition);
                    $file = $this->getContainer()->getParameter('kernel.data_dir') . DIRECTORY_SEPARATOR . $fileName;

                    if ($fs->exists($file) === true && $hulpvraag->getBestandsnaam() !== null && $hulpvraag->getBestandsnaam() !== '') {
                        $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'File exists, remove', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'file' => $file]);
                        $fs->remove($file);
                        $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'File removed', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'file' => $file]);
                    } else {
                        $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'No file exists', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'file' => $file]);
                    }
                }

                // set empty
                $hulpvraag->setEmail('');
                $hulpvraag->setTelefoon('');
                $hulpvraag->setVraag('');

                // set archief status
                $hulpvraag->setArchief(true);

                $this->getContainer()->get('doctrine')->getManager()->flush();

                $fnOutput(OutputInterface::VERBOSITY_DEBUG, 'End clean up', ['hulpvraagUuid' => $hulpvraag->getUuid()]);
            } catch (\Exception $e) {
                $fnOutput(OutputInterface::VERBOSITY_NORMAL, 'Error during clean up', ['hulpvraagUuid' => $hulpvraag->getUuid(), 'error' => $e->getMessage()]);
            }
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