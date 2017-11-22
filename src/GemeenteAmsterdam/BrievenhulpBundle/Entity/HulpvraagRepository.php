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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;
use GemeenteAmsterdam\BrievenhulpBundle\Form\DataTransformer\TelTransformer;

class HulpvraagRepository extends EntityRepository
{
    /**
     * @return Hulpvraag[]
     */
    public function findHulpvragenWithStatusNietToegewezen()
    {
        $qb = $this->createQueryBuilder('hulpvraag');
        $qb->andWhere('hulpvraag.status = :status');
        $qb->setParameter('status', -1);
        $qb->addOrderBy('hulpvraag.inkomstDatumTijd', 'ASC');

        return $qb->getQuery()->execute();
    }

    /**
     * @param Hulpverlener $hulpverlener
     * @param array $status
     * @param string $telefoon
     * @param number $firstResult
     * @param number $maxResults
     * @param string $sortColumn
     * @param string $sortDirection
     * @return Hulpvraag[]|array
     */
    public function search(Hulpverlener $hulpverlener = null, array $status = null, $archief = false, $telefoon = null, $firstResult = null, $maxResults = null, $sortColumn = 'datum', $sortDirection = 'DESC')
    {
        if ($status === null) {
            $status = [Hulpvraag::STATUS_NIETTOEGEWEZEN, Hulpvraag::STATUS_TOEGEWEZEN, Hulpvraag::STATUS_AFGEHANDELD, Hulpvraag::STATUS_GEEN_ANTWOORD, Hulpvraag::STATUS_SPAM];
        }

        if ($telefoon === null) {
            $telefoon = '%';
        } else {
            $telTransformer = new TelTransformer();
            $telefoon = $telTransformer->reverseTransform($telefoon);
            $telefoon = '%' . $telefoon . '%';
        }

        if (in_array($sortColumn, ['datum', 'telefoon', 'hulpverlener', 'organisatie', 'status', 'referentie']) === false) {
            $sortColumn = 'datum';
        }

        if (in_array($sortDirection, ['ASC', 'DESC']) === false) {
            $sortColumn = 'DESC';
        }


        $qb = $this->createQueryBuilder('hulpvraag');

        $orX = $qb->expr()->orX();
        foreach ($status as $s) {
            $orX->add($qb->expr()->eq('hulpvraag.status', $s));
        }
        $qb->andWhere($orX);

        $qb->andWhere('hulpvraag.telefoon LIKE :telefoon');
        $qb->setParameter('telefoon', $telefoon);

        $qb->leftJoin('hulpvraag.toegewezenHulpverlener', 'hulpverlener');
        $qb->addSelect('hulpverlener');

        $qb->leftJoin('hulpvraag.referentie', 'referentie');
        $qb->addSelect('referentie');

        if ($hulpverlener !== null) {
            $qb->andWhere('hulpverlener = :hulpverlener');
            $qb->setParameter('hulpverlener', $hulpverlener);
        }

        if ($archief === false) {
            $qb->andWhere('hulpvraag.archief = :archief');
            $qb->setParameter('archief', false);
        } else if ($archief === true) {
            $qb->andWhere('hulpvraag.archief = :archief');
            $qb->setParameter('archief', true);
        }

        $sortColumPaths = [
            'datum' => 'hulpvraag.inkomstDatumTijd',
            'telefoon' => 'hulpvraag.telefoon',
            'hulpverlener' => 'hulpverlener.naam',
            'organisatie' => 'hulpverlener.organisatie',
            'status' => 'hulpvraag.status',
            'referentie' => 'referentie.id',
        ];
        $qb->addOrderBy($sortColumPaths[$sortColumn], $sortDirection);

        $query = $qb->getQuery();

        if (null !== $maxResults) {
            $query->setMaxResults($maxResults);
        }

        if (null !== $firstResult) {
            $query->setFirstResult($firstResult);
        }

        return new Paginator($query);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ORM\EntityRepository::find()
     * @return Hulpvraag
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ORM\EntityRepository::findAll()
     * @return Hulpvraag[]|array
     */
    public function findAll()
    {
        return $this->findBy([], ['inkomstDatumTijd' => 'DESC']);
    }

    /**
     * @param string $bewaarPeriode
     * @return Hulpvraag[]
     */
    public function findHulpvragenForCleanUp($bewaarPeriode)
    {
        $datum = new \DateTime();
        $datum->sub(new \DateInterval($bewaarPeriode));

        $qb = $this->createQueryBuilder('hulpvraag');
        $qb->andWhere('hulpvraag.archief = :archief');
        $qb->setParameter('archief', false);
        $qb->andWhere('hulpvraag.inkomstDatumTijd < :datum');
        $qb->setParameter('datum', $datum);
        $qb->addOrderBy('hulpvraag.inkomstDatumTijd', 'ASC');

        return $qb->getQuery()->execute();
    }

    /**
     * @return array
     */
    public function getAantalHulpvragenPerHulpverlener()
    {
        $q = $this->_em->createQuery('
            SELECT
                hulpverlener.id AS hulpverlener_id,
                hulpvraag.status AS status,
                COUNT(hulpvraag.uuid) AS aantal
            FROM ' . $this->_entityName . ' AS hulpvraag
            JOIN hulpvraag.toegewezenHulpverlener AS hulpverlener
            GROUP BY hulpverlener.id, hulpvraag.status
            ORDER BY hulpverlener.id, hulpvraag.status
        ');

        $data = $q->execute(null, Query::HYDRATE_ARRAY);

        $return = [];
        foreach ($data as $row) {
            if (isset($return[$row['hulpverlener_id']]) === false) {
                $return[$row['hulpverlener_id']] = [];
            }
            if (isset($return[$row['hulpverlener_id']][$row['status']]) === false) {
                $return[$row['hulpverlener_id']][$row['status']] = 0;
            }
            $return[$row['hulpverlener_id']][$row['status']] = $row['aantal'];
        }
        return $return;
    }
}