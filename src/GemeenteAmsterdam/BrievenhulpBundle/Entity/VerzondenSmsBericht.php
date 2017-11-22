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
 * @ORM\Entity()
 * @ORM\Table
 */
class VerzondenSmsBericht
{
    /**
     * @var number
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $datumtijd;

    /**
     * @var Hulpvraag
     * @ORM\ManyToOne(targetEntity="Hulpvraag", inversedBy="verzondenSmsBerichten")
     * @ORM\JoinColumn(name="hulpvraag_uuid", referencedColumnName="uuid", nullable=false)
     */
    private $hulpvraag;

    /**
     * @var Hulpvraag
     * @ORM\ManyToOne(targetEntity="Hulpverlener")
     * @ORM\JoinColumn(name="hulpverlener_uuid", referencedColumnName="id", nullable=false)
     */
    private $hulpverlener;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $bericht;

    public function __construct()
    {
        $this->datumtijd = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDatumtijd()
    {
        return $this->datumtijd;
    }

    /**
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag
     */
    public function getHulpvraag()
    {
        return $this->hulpvraag;
    }

    /**
     * @param Hulpvraag $hulpvraag
     */
    public function setHulpvraag(Hulpvraag $hulpvraag = null)
    {
        $this->hulpvraag = $hulpvraag;
    }

    /**
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag
     */
    public function getHulpverlener()
    {
        return $this->hulpverlener;
    }

    /**
     * @param Hulpverlener $hulpverlener
     */
    public function setHulpverlener(Hulpverlener $hulpverlener = null)
    {
        $this->hulpverlener = $hulpverlener;
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