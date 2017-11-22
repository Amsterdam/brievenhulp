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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReassignHulpvraagFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hulpverlener', EntityType::class, ['required' => false, 'label' => 'Hulpverlener', 'class' => 'GemeenteAmsterdamBrievenhulpBundle:Hulpverlener', 'query_builder' => function (EntityRepository $repository) { return $repository->createQueryBuilder('hulpverlener')->andWhere('hulpverlener.isActive = :active')->setParameter('active', true)->addOrderBy('hulpverlener.naam'); }, 'multiple' => false, 'expanded' => false, 'placeholder' => '', 'error_bubbling' => true])
            ->add('bericht', TextareaType::class, ['required' => false, 'label' => 'Bericht', 'error_bubbling' => true])
            ->add('submitReassign', Types\SubmitType::class, ['label' => 'Toewijzen', 'attr' => ['class' => 'assign']])
        ;
    }
}