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
 * @ORM\Entity(repositoryClass="AuditLogEntryRepository")
 * @ORM\Table
 */
class AuditLogEntry
{
    /**
     * @var number
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $datumtijd;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $actie;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $route;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $ip;

    /**
     * @var string
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $referer;

    /**
     * @var Hulpverlener|NULL
     * @ORM\ManyToOne(targetEntity="Hulpverlener")
     * @ORM\JoinColumn(name="hulpverlener_id", referencedColumnName="id", nullable=true)
     */
    private $hulpverlener;

    /**
     * @var Hulpvraag|NULL
     * @ORM\ManyToOne(targetEntity="Hulpvraag")
     * @ORM\JoinColumn(name="hulpvraag_uuid", referencedColumnName="uuid", nullable=true)
     */
    private $hulpvraag;

    /**
     * @var string
     * @ORM\Column(type="json_array", nullable=false)
     */
    private $data;

    /**
     * @param \DateTime $datumtijd
     * @param string $route
     * @param string $actie
     * @param string $ip
     * @param string $referer
     * @param Hulpverlener $hulpverlener
     * @param Hulpvraag $hulpvraag
     */
    public function __construct(\DateTime $datumtijd, $route, $actie, $ip, $referer = null, Hulpverlener $hulpverlener = null, Hulpvraag $hulpvraag = null, $data = [])
    {
        $this->datumtijd = $datumtijd;
        $this->actie = $actie;
        $this->route = $route;
        $this->ip = $ip;
        $this->referer = $referer;
        $this->hulpverlener = $hulpverlener;
        $this->hulpvraag = $hulpvraag;
        $this->data = $data;
    }

    /**
     * @return \DateTime
     */
    public function getDatumtijd()
    {
        return $this->datumtijd;
    }

    public function getActie()
    {
        return $this->actie;
    }

    /**
     * @param string $actie
     */
    public function setActie($actie)
    {
        $this->actie = $actie;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener|NULL
     */
    public function getHulpverlener()
    {
        return $this->hulpverlener;
    }

    /**
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag|NULL
     */
    public function getHulpvraag()
    {
        return $this->hulpvraag;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setData($key, $value)
    {
        $data = $this->data;
        $data[$key] = $value;
        $this->data = $data;
    }
}