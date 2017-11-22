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
class Referentie
{
    /**
     * @var number
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Hulpvraag
     * @ORM\OneToOne(targetEntity="Hulpvraag", inversedBy="referentie")
     * @ORM\JoinColumn(name="hulpvraag_uuid", referencedColumnName="uuid", nullable=false)
     */
    private $hulpvraag;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hulpvraag
     *
     * @param \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag $hulpvraag
     *
     * @return Referentie
     */
    public function setHulpvraag(\GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag $hulpvraag)
    {
        $this->hulpvraag = $hulpvraag;

        return $this;
    }

    /**
     * Get hulpvraag
     *
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag
     */
    public function getHulpvraag()
    {
        return $this->hulpvraag;
    }
}