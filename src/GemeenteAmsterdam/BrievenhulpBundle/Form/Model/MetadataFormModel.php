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

namespace GemeenteAmsterdam\BrievenhulpBundle\Form\Model;

use GemeenteAmsterdam\BrievenhulpBundle\Entity\Afzender;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Tag;
use Doctrine\Common\Collections\Collection;

class MetadataFormModel
{
    public $afzender;

    public $tags;

    /**
     * @param Afzender|NULL $afzender
     * @param Tag[]|ArrayCollection|array|NULL $tags
     */
    public function __construct(Afzender $afzender = null, $tags = null)
    {
        if ($afzender !== null) {
            $this->afzender = $afzender->__toString();
        }

        if ($tags !== null && count($tags) !== 0) {
            $tags = $tags instanceof Collection ? $tags->toArray() : $tags;
            $this->tags = implode(', ', $tags);
        }
    }
}