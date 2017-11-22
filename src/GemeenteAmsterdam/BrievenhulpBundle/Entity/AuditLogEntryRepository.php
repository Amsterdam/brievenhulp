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
use Doctrine\Common\Collections\ArrayCollection;

class AuditLogEntryRepository extends EntityRepository
{
    /**
     * {@inheritDoc}
     * @see \Doctrine\ORM\EntityRepository::find()
     * @return AuditLogEntry
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ORM\EntityRepository::findOneBy()
     * @return AuditLogEntry
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * @param Hulpverlener $subject
     * @param \DateTime $date
     * @return ArrayCollection|AuditLogEntry[]
     */
    public function findBySubjectAndDate(Hulpverlener $subject, \DateTime $date)
    {
        $startDate = $date;
        $startDate->setTime(0, 0, 0);
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P1D'));

        $qb = $this->createQueryBuilder('audit_log_entry');
        $qb->andWhere('audit_log_entry.hulpverlener = :subject');
        $qb->setParameter('subject', $subject);
        $qb->andWhere('audit_log_entry.datumtijd BETWEEN :start_date AND :end_date');
        $qb->setParameter('start_date', $startDate);
        $qb->setParameter('end_date', $endDate);

        return $qb->getQuery()->execute();
    }
}