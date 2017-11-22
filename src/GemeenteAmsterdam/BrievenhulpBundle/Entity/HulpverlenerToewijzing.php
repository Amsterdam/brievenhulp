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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class HulpverlenerToewijzing
{
    /**
     * @var number
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Hulpverlener
     * @ORM\ManyToOne(targetEntity="Hulpverlener")
     * @ORM\JoinColumn(name="hulpverlener_id", referencedColumnName="id")
     */
    private $hulpverlener;

    /**
     * @var Hulpvraag
     * @ORM\ManyToOne(targetEntity="Hulpvraag", inversedBy="hulpverlenerToewijzingen")
     * @ORM\JoinColumn(name="hulpvraag_uuid", referencedColumnName="uuid")
     */
    private $hulpvraag;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $datumtijd;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $info;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $bericht;

    public function getId()
    {
        return $this->id;
    }

    public function getHulpverlener()
    {
        return $this->hulpverlener;
    }

    public function setHulpverlener(Hulpverlener $hulpverlener)
    {
        $this->hulpverlener = $hulpverlener;
    }

    public function getHulpvraag()
    {
        return $this->hulpvraag;
    }

    public function setHulpvraag(Hulpvraag $hulpvraag = null)
    {
        $this->hulpvraag = $hulpvraag;
        if ($hulpvraag !== null && $hulpvraag->hasHulpverlenerToewijzing($this) === false)
            $hulpvraag->addHulpverlenerToewijzing($this);
    }

    public function getDatumtijd()
    {
        return $this->datumtijd;
    }

    public function setDatumtijd(\DateTime $datumtijd)
    {
        $this->datumtijd = $datumtijd;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getBericht()
    {
        return $this->bericht;
    }

    public function setBericht($bericht)
    {
        $this->bericht = $bericht;
    }
}