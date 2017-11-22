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

class HulpverlenerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam', Types\TextType::class, ['required' => true])
            ->add('organisatie', Types\TextType::class, ['required' => true])
            ->add('email', Types\EmailType::class, ['required' => true])
            ->add('telefoon', TelType::class, ['required' => true])
            ->add('isActive', Types\CheckboxType::class, ['label' => 'Actief', 'required' => false])
            ->add('password', Types\PasswordType::class, ['label' => 'Wachtwoord'])
            ->add('role', Types\ChoiceType::class, [
                'choices' => ['Gebruiker' => Hulpverlener::ROLE_USER, 'Beheerder' => Hulpverlener::ROLE_ADMIN],
                'label'   => 'Rol'
            ])
            ->add('beschikbaarheid', Types\ChoiceType::class, ['expanded' => true, 'multiple' => true, 'choices' => HulpverlenerFormType::getBeschikbaarheidOptions()])
            ->add('updatesPerSms', Types\CheckboxType::class, ['label' => 'Updates ook per SMS', 'required' => false])
        ;
    }

    public static function getBeschikbaarheidOptions()
    {
        return [
            'Maandag (ochtend)' => 'mon1',
            'Maandag (middag)' => 'mon2',
            'Dinsdag (ochtend)' => 'tue1',
            'Dinsdag (middag)' => 'tue2',
            'Woensdag (ochtend)' => 'wed1',
            'Woensdag (middag)' => 'wed2',
            'Donderdag (ochtend)' => 'thu1',
            'Donderdag (middag)' => 'thu2',
            'Vrijdag (ochtend)' => 'fri1',
            'Vrijdag (middag)' => 'fri2',
            'Zaterdag (ochtend)' => 'sat1',
            'Zaterdag (middag)' => 'sat2',
            'Zondag (ochtend)' => 'sun1',
            'Zondag (middag)' => 'sun2'
        ];
    }
}