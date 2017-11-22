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

namespace GemeenteAmsterdam\BrievenhulpBundle\Entity;

use Doctrine\ORM\EntityRepository;

class HulpverlenerRepository extends EntityRepository
{
    /**
     * {@inheritDoc}
     * @see \Doctrine\ORM\EntityRepository::find()
     * @return Hulpverlener
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ORM\EntityRepository::findOneBy()
     * @return Hulpverlener
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    public function getAantalToegewezenHulpvragenPerHulpverlenerWithMinStatus($status)
    {
        $today = new \DateTime();
        $today->setTime(0,0,0);
        $qb = $this->createQueryBuilder('hv');
        $qb->leftJoin('hv.toegewezenHulpvragen', 'hulpvraag');
        $qb->andWhere('hulpvraag.status >= :status');
        $qb->andWhere('hulpvraag.startDatumTijd >= :today');
        $qb->setParameter('status', $status);
        $qb->setParameter('today',  $today);
        $qb->groupBy('hv.id');
        $qb->select('hv AS hulpverlener');
        $qb->addSelect($qb->expr()->count('hulpvraag.uuid') . ' AS load');

        return $qb->getQuery()->execute();
    }
}