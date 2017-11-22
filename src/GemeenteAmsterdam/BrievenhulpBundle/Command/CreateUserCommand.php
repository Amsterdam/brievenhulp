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
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener;
use GemeenteAmsterdam\BrievenhulpBundle\Form\DataTransformer\TelTransformer;

class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('brievenhulp:create-user');
        $this->addArgument('naam', InputArgument::REQUIRED, 'Naam van de gebruiker');
        $this->addArgument('organisatie', InputArgument::REQUIRED, 'Organisatie van de gebruiker');
        $this->addArgument('email', InputArgument::REQUIRED, 'E-mail adres van de gebruiker');
        $this->addArgument('telefoon', InputArgument::REQUIRED, 'Telefoon nummer van de gebruiker');
        $this->addArgument('password', InputArgument::REQUIRED, 'In te stellen wachtwoord');
        $this->addArgument('role', InputArgument::REQUIRED, 'Rol: ' . Hulpverlener::ROLE_USER . ' of ' . Hulpverlener::ROLE_ADMIN);
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $telTransformer = new TelTransformer();
        $telefoon = $telTransformer->reverseTransform($input->getArgument('telefoon'));

        $hulpverlener = new Hulpverlener();
        $hulpverlener->setEmail($input->getArgument('email'));
        $hulpverlener->setNaam($input->getArgument('naam'));
        $hulpverlener->setOrganisatie($input->getArgument('organisatie'));
        $hulpverlener->setTelefoon($telefoon);
        $hulpverlener->setSecret(md5(time() . microtime(true) . uniqid(null, true)));
        $hulpverlener->setSmsSjabloon('Wij hebben u vandaag gebeld. We konden u echter niet bereiken. Vriendelijke groet, Snap de Brief (U kunt niet reageren op dit bericht). We called you today. But we couldn\'t reach you. Kind regards, Snap de Brief (You cannot reply to this message)');

        $role = $input->getArgument('role');
        if (!in_array($role, [Hulpverlener::ROLE_USER, Hulpverlener::ROLE_ADMIN])) {
            throw new \Exception('Onbekende rol, gebruik: ' . Hulpverlener::ROLE_USER . ' of ' . Hulpverlener::ROLE_ADMIN);
        }
        $hulpverlener->setRole($role);

        $encoder = $this->getContainer()->get('security.password_encoder');
        $encoded = $encoder->encodePassword($hulpverlener, $input->getArgument('password'));
        $hulpverlener->setPassword($encoded);

        $this->getContainer()->get('doctrine')->getManager()->persist($hulpverlener);
        $this->getContainer()->get('doctrine')->getManager()->flush();

        $output->writeln('User created');
    }
}