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
use GemeenteAmsterdam\BrievenhulpBundle\Form\Model\SubmitRequestFormModel;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\TelType;

class SubmitRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('foto', Types\FileType::class, ['required' => false])
            ->add('vraag', Types\TextareaType::class, ['required' => false])
            //->add('methode', Types\ChoiceType::class, ['required' => true, 'expanded' => true, 'choice_attr' => function($val) { return ['data-handler' => 'select-'. $val]; }, 'choices' => array_combine(array_values(SubmitRequestFormModel::getMethodes()), array_keys(SubmitRequestFormModel::getMethodes())) ])
            ->add('methode', Types\HiddenType::class, ['required' => true])
            //->add('emailadres', Types\EmailType::class, ['required' => false])
            ->add('emailadres', Types\HiddenType::class, ['required' => false])
            ->add('telefoon', TelType::class, ['required' => false])
            ->add('taal', Types\ChoiceType::class, ['required' => true, 'expanded' => true, 'choices' => array_combine(array_values(SubmitRequestFormModel::getTalen()), array_keys(SubmitRequestFormModel::getTalen()))])
        ;
    }
}