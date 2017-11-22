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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Types;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\TelType;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam', Types\TextType::class, ['required' => true])
            ->add('organisatie', Types\TextType::class, ['required' => true])
            ->add('email', Types\EmailType::class, ['required' => true])
            ->add('telefoon', TelType::class, ['required' => true])
            ->add('smssjabloon', Types\TextareaType::class, ['required' => false, 'label' => 'SMS sjabloon'])
            ->add('beschikbaarheid', Types\ChoiceType::class, ['expanded' => true, 'multiple' => true, 'choices' => HulpverlenerFormType::getBeschikbaarheidOptions()])
            ->add('updatesPerSms', Types\CheckboxType::class, ['label' => 'Updates ook per SMS', 'required' => false])
        ;
    }
}