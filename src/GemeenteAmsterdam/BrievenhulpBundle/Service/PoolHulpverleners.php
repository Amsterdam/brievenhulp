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

namespace GemeenteAmsterdam\BrievenhulpBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener;

class PoolHulpverleners implements \Countable
{
    /**
     * @var ArrayCollection|Hulpverlener[]
     */
    protected $pool;

    /**
     * @var ArrayCollection|Hulpverlener[]
     */
    protected $orginal;

    /**
     * @var array
     */
    protected $loadInformation;

    /**
     * @param array|Hulpverlener[] $hulpverleners
     */
    public function __construct(array $hulpverleners)
    {
        $this->orginal = new ArrayCollection($hulpverleners);
        $this->reset();
    }

    /**
     * Breng de pool terug naar initialisatie staat
     */
    public function reset()
    {
        $this->pool = clone $this->orginal;
        $this->loadInformation = [];
        foreach ($this->pool as $hulpverlener) {
            $this->loadInformation[$hulpverlener->getId()] = 0;
        }
    }

    /**
     * Filter de pool op beschikbaarheid
     * @param string $dag Naam van dag/dagdeel
     */
    public function filterBeschikbaarheid($dag)
    {
        $this->pool = $this->pool->filter(function (Hulpverlener $hulpverlener) use ($dag) {
            return in_array($dag, $hulpverlener->getBeschikbaarheid());
        });
    }

    /**
     * Stel informatie over load (werkdruk) van hulpverlener in
     * @param Hulpverlener $hulpverlener
     * @param number $load 0 = geen
     */
    public function populateLoadInformation(Hulpverlener $hulpverlener, $load)
    {
        $this->loadInformation[$hulpverlener->getId()] = $load;
    }

    /**
     * Onderzoek de pool op de laagst voorkomende load (werkdruk) waarde
     * @return number
     */
    public function selectLowestLoad()
    {
        $lowestLoad = null;
        foreach ($this->pool as $hulpverlener) {
            /* @var $hulpverlener Hulpverlener */
            if ($lowestLoad === null || $this->loadInformation[$hulpverlener->getId()] < $lowestLoad) {
                $lowestLoad = $this->loadInformation[$hulpverlener->getId()];
            }
        }
        return $lowestLoad;
    }

    /**
     * Filter de pool op hulpverleners met een specifieke load (werkdruk) waarde
     * @param number $load
     */
    public function filterForLoad($load)
    {
        $loadInformation = $this->loadInformation;
        $this->pool = $this->pool->filter(function (Hulpverlener $hulpverlener) use ($load, $loadInformation) {
            return ($loadInformation[$hulpverlener->getId()] === $load);
        });
    }

    /**
     * Selecteer een willekeurig iemand uit de pool
     * @return Hulpverlener|NULL
     */
    public function getRandom()
    {
        if ($this->pool->count() === 0)
            return null;
        $pool = $this->pool->toArray();
        $key = array_rand($pool, 1);
        return $pool[$key];
    }

    /**
     * {@inheritDoc}
     * @see Countable::count()
     */
    public function count()
    {
        return $this->pool->count();
    }
}