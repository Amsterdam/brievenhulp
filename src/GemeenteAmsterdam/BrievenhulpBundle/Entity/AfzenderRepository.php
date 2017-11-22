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

class AfzenderRepository extends EntityRepository
{
    /**
     * @param string $naam
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Afzender|NULL
     */
    public function getOrCreate($naam)
    {
        $naam = trim(strtolower($naam));
        if ($naam === '' || $naam === null) {
            return null;
        }
        $afzender = $this->find($naam);
        if ($afzender === null) {
            $afzender = new Afzender();
            $afzender->setNaam($naam);
            $this->_em->persist($afzender);
            $this->_em->flush($afzender);
        }
        return $afzender;
    }
}