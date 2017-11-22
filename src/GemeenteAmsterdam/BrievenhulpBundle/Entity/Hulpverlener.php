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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="HulpverlenerRepository")
 * @ORM\Table
 */
class Hulpverlener implements UserInterface, \Serializable
{
    const ROLE_USER  = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var number
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $secret;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $naam;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $organisatie;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $smsSjabloon;

    /**
     * @var string
     * @ORM\Column(type="string", length=12, nullable=false)
     */
    private $telefoon;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $role;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $beschikbaarheid;

    /**
     * @var Hulpvraag[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Hulpvraag", mappedBy="toegewezenHulpverlener", orphanRemoval=true)
     * @ORM\OrderBy({"inkomstDatumTijd" = "DESC"})
     */
    private $toegewezenHulpvragen;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $password;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $updatesPerSms;

    public function __construct()
    {
        $this->toegewezenHulpvragen = new ArrayCollection();
        $this->isActive = true;
        $this->beschikbaarheid = array();
        $this->updatesPerSms = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getNaam()
    {
        return $this->naam;
    }

    public function setNaam($naam)
    {
        $this->naam = $naam;
    }

    public function getOrganisatie()
    {
        return $this->organisatie;
    }

    public function setOrganisatie($organisatie)
    {
        $this->organisatie = $organisatie;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getTelefoon()
    {
        return $this->telefoon;
    }

    public function setTelefoon($telefoon)
    {
        $this->telefoon = $telefoon;
    }

    /**
     * @return array Lijst met dag(delen), voorbeeld mon1, mon2, fri1, sun2
     */
    public function getBeschikbaarheid()
    {
        return $this->beschikbaarheid;
    }

    public function setBeschikbaarheid(array $beschikbaarheid)
    {
        $this->beschikbaarheid = $beschikbaarheid;
    }

    /**
     * @return Hulpvraag[]|ArrayCollection
     */
    public function getToegewezenHulpvragen()
    {
        return $this->toegewezenHulpvragen;
    }

    /**
     * @return number
     */
    public function countToegewezenHulpvragen()
    {
        return $this->toegewezenHulpvragen->count();
    }

    /**
     * @param Hulpvraag $hulpvraag
     * @return boolean
     */
    public function hasToegewezenHulpvraag(Hulpvraag $hulpvraag)
    {
        return $this->toegewezenHulpvragen->contains($hulpvraag);
    }

    /**
     * @param Hulpvraag $hulpvraag
     */
    public function addToegewezenHulpvraag(Hulpvraag $hulpvraag)
    {
        if ($this->hasToegewezenHulpvraag($hulpvraag) === false) {
            $this->toegewezenHulpvragen->add($hulpvraag);
        }
        if ($hulpvraag->getToegewezenHulpverlener() !== $this) {
            $hulpvraag->setToegewezenHulpverlener($this);
        }
    }

    /**
     * @param Hulpvraag $hulpvraag
     */
    public function removeToegewezenHulpvraag(Hulpvraag $hulpvraag)
    {
        if ($this->hasToegewezenHulpvraag($hulpvraag) === true) {
            $this->toegewezenHulpvragen->removeElement($hulpvraag);
        }
        if ($hulpvraag->getToegewezenHulpverlener() === $this) {
            $hulpvraag->setToegewezenHulpverlener(null);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getUsername()
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getSalt()
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getPassword()
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password Encrypted password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getRoles()
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::eraseCredentials()
     */
    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    public function __toString()
    {
        return $this->naam . ' (' . $this->organisatie . ')';
    }

    /**
     * Set smsSjabloon
     *
     * @param string $smsSjabloon
     *
     * @return Hulpverlener
     */
    public function setSmsSjabloon($smsSjabloon)
    {
        $this->smsSjabloon = $smsSjabloon;

        return $this;
    }

    /**
     * Get smsSjabloon
     *
     * @return string
     */
    public function getSmsSjabloon()
    {
        return $this->smsSjabloon;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return Hulpverlener
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Hulpverlener
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    public function getUpdatesPerSms()
    {
        return $this->updatesPerSms;
    }

    public function setUpdatesPerSms($updatesPerSms)
    {
        $this->updatesPerSms = $updatesPerSms;
    }
}