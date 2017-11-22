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

namespace GemeenteAmsterdam\BrievenhulpBundle\Form\Type;

use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Types;

class HulpverlenerEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam', Types\TextType::class, ['required' => true])
            ->add('organisatie', Types\TextType::class, ['required' => true])
            ->add('email', Types\EmailType::class, ['required' => true])
            ->add('telefoon', TelType::class, ['required' => true])
            ->add('isActive', Types\CheckboxType::class, ['label' => 'Actief', 'required' => false])
            ->add('password', Types\PasswordType::class, ['label' => 'Wachtwoord', 'required' => false])
            ->add('role', Types\ChoiceType::class, [
                'choices' => ['Gebruiker' => Hulpverlener::ROLE_USER, 'Beheerder' => Hulpverlener::ROLE_ADMIN],
                'label'   => 'Rol'
            ])
            ->add('updatesPerSms', Types\CheckboxType::class, ['label' => 'Updates ook per SMS', 'required' => false])
            ->add('beschikbaarheid', Types\ChoiceType::class, ['expanded' => true, 'multiple' => true, 'choices' => HulpverlenerFormType::getBeschikbaarheidOptions()])
        ;
    }
}