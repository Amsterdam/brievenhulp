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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class SetPasswordCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('brievenhulp:set-password');
        $this->addArgument('email', InputArgument::REQUIRED, 'E-mail adres van de gebruiker');
        $this->addArgument('password', InputArgument::REQUIRED, 'In te stellen wachtwoord');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $hulpverlenerRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerRepository */
        $hulpverlenerRepository = $this->getContainer()->get('gemeente_amsterdam_brievenhulp.repositories.hulpverlener');

        $hulpverlener = $hulpverlenerRepository->findOneBy(['email' => $input->getArgument('email')]);
        if ($hulpverlener === null) {
            $output->writeln('Can not find hulpverlener with email "' . $input->getArgument('email') . '"');
            return;
        }

        $encoder = $this->getContainer()->get('security.password_encoder');
        $encoded = $encoder->encodePassword($hulpverlener, $input->getArgument('password'));
        $hulpverlener->setPassword($encoded);

        $this->getContainer()->get('doctrine')->getManager()->flush();

        $output->writeln('Password set!');
    }
}