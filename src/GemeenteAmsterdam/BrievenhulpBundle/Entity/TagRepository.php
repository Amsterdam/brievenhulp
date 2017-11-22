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

class TagRepository extends EntityRepository
{
    /**
     * @param string $naam
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Tag|NULL
     */
    public function getOrCreate($naam)
    {
        $naam = trim(strtolower($naam));
        $tag = $this->find($naam);
        if ($tag === null) {
            $tag = new Tag();
            $tag->setNaam($naam);
            $this->_em->persist($tag);
            $this->_em->flush($tag);
        }
        return $tag;
    }
}