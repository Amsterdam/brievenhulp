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

/**
 * @ORM\Entity(repositoryClass="HulpvraagRepository")
 * @ORM\Table
 */
class Hulpvraag
{
    /**
     * @var string
     */
    const METHODE_EMAIL = 'email';

    /**
     * @var string
     */
    const METHODE_SMS = 'sms';

    /**
     * @var string
     */
    const METHODE_TELEFOON = 'telefoon';

    /**
     * @var number
     */
    const STATUS_NIETTOEGEWEZEN = -1;

    /**
     * @var number
     */
    const STATUS_TOEGEWEZEN = 0;

    /**
     * @var number
     */
    const STATUS_AFGEHANDELD = 1;

    /**
     * @var number
     */
    const STATUS_GEEN_ANTWOORD = 2;

    /**
     * @var number
     */
    const STATUS_SPAM = 3;

    /**
     * @var number
     */
    const UPLOADSTYLE_CLASSIC = 0b000;

    /**
     * @var number
     */
    const UPLOADSTYLE_BASE64 =  0b001;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=36, nullable=false)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $secret;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $vraag;

    /**
     * @var string
     * @ORM\Column(type="string", length=8, nullable=false)
     */
    private $methode;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $telefoon;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $inkomstDatumTijd;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDatumTijd;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $eersteKeerGeopendDatumTijd;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $eindDatumTijd;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $geenHulpverlenerBeschikbaarReminder;

    /**
     * @var Hulpverlener
     * @ORM\ManyToOne(targetEntity="Hulpverlener", inversedBy="toegewezenHulpvragen")
     * @ORM\JoinColumn(name="hulpverlener_id", referencedColumnName="id")
     */
    private $toegewezenHulpverlener;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bestandsnaam;

    /**
     * @var string
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    private $taal;

    /**
     * @var string
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    private $interfaceTaal;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $vraagVolgensHulpverlener;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $reactieVanHulpverlener;

    /**
     * @var number
     * @ORM\Column(type="integer", nullable=false)
     */
    private $status;

    /**
     * @var VerzondenSmsBericht[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="VerzondenSmsBericht", mappedBy="hulpvraag")
     * @ORM\OrderBy({"datumtijd"="DESC","id"="DESC"})
     */
    private $verzondenSmsBerichten;

    /**
     * @var HulpverlenerToewijzing[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="HulpverlenerToewijzing", mappedBy="hulpvraag")
     * @ORM\OrderBy({"datumtijd"="DESC","id"="DESC"})
     */
    private $hulpverlenerToewijzingen;

    /**
     * @var number
     * @ORM\Column(type="integer", nullable=false)
     */
    private $uploadStyle;

    /**
     * @var Referentie
     * @ORM\OneToOne(targetEntity="Referentie", mappedBy="hulpvraag")
     */
    private $referentie;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $archief;

    /**
     * @var Afzender
     * @ORM\ManyToOne(targetEntity="Afzender")
     * @ORM\JoinColumn(name="afzender_naam", referencedColumnName="naam", nullable=true)
     */
    private $afzender;

    /**
     * @var Tag[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(
     *  joinColumns={@ORM\JoinColumn(name="hulpvraag_uuid", referencedColumnName="uuid")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="tag_naam", referencedColumnName="naam")}
     * )
     */
    private $tags;

    public function __construct()
    {
        $this->verzondenSmsBerichten = new ArrayCollection();
        $this->archief = false;
        $this->tags = new ArrayCollection();
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getVraag()
    {
        return $this->vraag;
    }

    public function setVraag($vraag)
    {
        $this->vraag = $vraag;
    }

    public function getMethode()
    {
        return $this->methode;
    }

    public function setMethode($methode)
    {
        if (in_array($methode, [self::METHODE_EMAIL, self::METHODE_SMS, self::METHODE_TELEFOON]) === false) {
            throw new \InvalidArgumentException('Invalid value for methode "' . $methode . '"');
        }
        $this->methode = $methode;
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
     * @return \DateTime
     */
    public function getInkomstDatumTijd()
    {
        return $this->inkomstDatumTijd;
    }

    /**
     * @param \DateTime $inkomstDatumTijd
     */
    public function setInkomstDatumTijd(\DateTime $inkomstDatumTijd)
    {
        $this->inkomstDatumTijd = $inkomstDatumTijd;
    }

    /**
     * @return \DateTime
     */
    public function getStartDatumTijd()
    {
        return $this->startDatumTijd;
    }

    /**
     * @param \DateTime $startDatumTijd
     */
    public function setStartDatumTijd(\DateTime $startDatumTijd)
    {
        $this->startDatumTijd = $startDatumTijd;
    }

    /**
     * @return \DateTime
     */
    public function getEersteKeerGeopendDatumTijd()
    {
        return $this->eersteKeerGeopendDatumTijd;
    }

    /**
     * @param \DateTime $eersteKeerGeopendDatumTijd
     */
    public function setEersteKeerGeopendDatumTijd(\DateTime $eersteKeerGeopendDatumTijd)
    {
        $this->eersteKeerGeopendDatumTijd = $eersteKeerGeopendDatumTijd;
    }

    /**
     * @return \DateTime
     */
    public function getEindDatumTijd()
    {
        return $this->eindDatumTijd;
    }

    /**
     * @param \DateTime $eindDatumTijd
     */
    public function setEindDatumTijd(\DateTime $eindDatumTijd)
    {
        $this->eindDatumTijd = $eindDatumTijd;
    }

    /**
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener
     */
    public function getToegewezenHulpverlener()
    {
        return $this->toegewezenHulpverlener;
    }

    /**
     * @param Hulpverlener $toegewezenHulpverlener
     */
    public function setToegewezenHulpverlener(Hulpverlener $toegewezenHulpverlener = null)
    {
        if ($toegewezenHulpverlener !== $this->toegewezenHulpverlener) {
            $this->toegewezenHulpverlener = $toegewezenHulpverlener;
        }
        if ($toegewezenHulpverlener !== null) {
            $toegewezenHulpverlener->addToegewezenHulpvraag($this);
        }
    }

    public function getBestandsnaam()
    {
        return $this->bestandsnaam;
    }

    public function setBestandsnaam($bestandsnaam)
    {
        $this->bestandsnaam = $bestandsnaam;
    }

    public function getTaal()
    {
        return $this->taal;
    }

    public function setTaal($taal)
    {
        $this->taal = $taal;
    }

    public function getInterfaceTaal()
    {
        return $this->interfaceTaal;
    }

    public function setInterfaceTaal($interfaceTaal)
    {
        $this->interfaceTaal = $interfaceTaal;
    }

    public function getVraagVolgensHulpverlener()
    {
        return $this->vraagVolgensHulpverlener;
    }

    public function setVraagVolgensHulpverlener($vraagVolgensHulpverlener)
    {
        $this->vraagVolgensHulpverlener = $vraagVolgensHulpverlener;
    }

    public function getReactieVanHulpverlener()
    {
        return $this->reactieVanHulpverlener;
    }

    public function setReactieVanHulpverlener($reactieVanHulpverlener)
    {
        $this->reactieVanHulpverlener = $reactieVanHulpverlener;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        if (in_array($status, [self::STATUS_NIETTOEGEWEZEN, self::STATUS_TOEGEWEZEN, self::STATUS_AFGEHANDELD, self::STATUS_GEEN_ANTWOORD, self::STATUS_SPAM]) === false)
            throw new \InvalidArgumentException('Status "' . $status . '" niet bekend');
        $this->status = $status;
    }

    public function getUploadStyle()
    {
        return $this->uploadStyle;
    }

    public function setUploadStyle($uploadStyle)
    {
        if (in_array($uploadStyle, [self::UPLOADSTYLE_CLASSIC, self::UPLOADSTYLE_BASE64]) === false)
            throw new \InvalidArgumentException('Upload style "' . $status . '" niet bekend');
        $this->uploadStyle = $uploadStyle;
    }

    /**
     * @param VerzondenSmsBericht $verzondenSmsBericht
     */
    public function addVerzondenSmsBericht(VerzondenSmsBericht $verzondenSmsBericht)
    {
        if ($this->hasVerzondenSmsBerichten($verzondenSmsBericht) === false)
            $this->verzondenSmsBerichten->add($verzondenSmsBericht);
        if ($verzondenSmsBericht->getHulpvraag() !== $this)
            $verzondenSmsBericht->setHulpvraag($this);
    }

    /**
     * @param VerzondenSmsBericht $verzondenSmsBericht
     * @return boolean
     */
    public function hasVerzondenSmsBerichten(VerzondenSmsBericht $verzondenSmsBericht)
    {
        return $this->verzondenSmsBerichten->contains($verzondenSmsBericht);
    }

    /**
     * @param VerzondenSmsBericht $verzondenSmsBericht
     */
    public function removeVerzondenSmsBericht(VerzondenSmsBericht $verzondenSmsBericht)
    {
        if ($this->hasVerzondenSmsBerichten($verzondenSmsBericht) === true)
            $this->verzondenSmsBerichten->removeElement($verzondenSmsBericht);
        if ($verzondenSmsBericht->getHulpvraag() === $this)
            $verzondenSmsBericht->setHulpvraag(null);
    }

    /**
     * @return HulpverlenerToewijzing[]|ArrayCollection
     */
    public function getHulpverlenerToewijzingen()
    {
        return $this->hulpverlenerToewijzingen;
    }

    /**
     * @param HulpverlenerToewijzing $hulpverlenerToewijzing
     */
    public function addHulpverlenerToewijzing(HulpverlenerToewijzing $hulpverlenerToewijzing)
    {
        if ($this->hasHulpverlenerToewijzing($hulpverlenerToewijzing) === false)
            $this->hulpverlenerToewijzingen->add($hulpverlenerToewijzing);
        if ($hulpverlenerToewijzing->getHulpvraag() !== $this)
            $hulpverlenerToewijzing->setHulpvraag($this);
    }

    /**
     * @param HulpverlenerToewijzing $hulpverlenerToewijzing
     * @return boolean
     */
    public function hasHulpverlenerToewijzing(HulpverlenerToewijzing $hulpverlenerToewijzing)
    {
        return $this->hulpverlenerToewijzingen->contains($hulpverlenerToewijzing);
    }

    /**
     * @param HulpverlenerToewijzing $hulpverlenerToewijzing
     */
    public function removeHulpverlenerToewijzing(HulpverlenerToewijzing $hulpverlenerToewijzing)
    {
        if ($this->hasHulpverlenerToewijzing($hulpverlenerToewijzing) === true)
            $this->hulpverlenerToewijzingen->removeElement($hulpverlenerToewijzing);
        if ($hulpverlenerToewijzing->getHulpvraag() === $this)
            $hulpverlenerToewijzing->setHulpvraag(null);
    }

    /**
     * Add verzondenSmsBerichten
     *
     * @param \GemeenteAmsterdam\BrievenhulpBundle\Entity\VerzondenSmsBericht $verzondenSmsBerichten
     *
     * @return Hulpvraag
     */
    public function addVerzondenSmsBerichten(\GemeenteAmsterdam\BrievenhulpBundle\Entity\VerzondenSmsBericht $verzondenSmsBerichten)
    {
        $this->verzondenSmsBerichten[] = $verzondenSmsBerichten;

        return $this;
    }

    /**
     * Remove verzondenSmsBerichten
     *
     * @param \GemeenteAmsterdam\BrievenhulpBundle\Entity\VerzondenSmsBericht $verzondenSmsBerichten
     */
    public function removeVerzondenSmsBerichten(\GemeenteAmsterdam\BrievenhulpBundle\Entity\VerzondenSmsBericht $verzondenSmsBerichten)
    {
        $this->verzondenSmsBerichten->removeElement($verzondenSmsBerichten);
    }

    /**
     * Get verzondenSmsBerichten
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVerzondenSmsBerichten()
    {
        return $this->verzondenSmsBerichten;
    }

    /**
     * Set referentie
     *
     * @param \GemeenteAmsterdam\BrievenhulpBundle\Entity\Referentie $referentie
     *
     * @return Hulpvraag
     */
    public function setReferentie(\GemeenteAmsterdam\BrievenhulpBundle\Entity\Referentie $referentie = null)
    {
        $this->referentie = $referentie;

        return $this;
    }

    /**
     * Get referentie
     *
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Referentie
     */
    public function getReferentie()
    {
        return $this->referentie;
    }

    /**
     * Set geenHulpverlenerBeschikbaarReminder
     *
     * @param \DateTime $geenHulpverlenerBeschikbaarReminder
     *
     * @return Hulpvraag
     */
    public function setGeenHulpverlenerBeschikbaarReminder($geenHulpverlenerBeschikbaarReminder)
    {
        $this->geenHulpverlenerBeschikbaarReminder = $geenHulpverlenerBeschikbaarReminder;

        return $this;
    }

    /**
     * Get geenHulpverlenerBeschikbaarReminder
     *
     * @return \DateTime
     */
    public function getGeenHulpverlenerBeschikbaarReminder()
    {
        return $this->geenHulpverlenerBeschikbaarReminder;
    }

    public function isArchief()
    {
        return $this->archief;
    }

    public function setArchief($archief)
    {
        $this->archief = $archief;
    }

    /**
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Afzender
     */
    public function getAfzender()
    {
        return $this->afzender;
    }

    /**
     * @param Afzender $afzender
     */
    public function setAfzender(Afzender $afzender = null)
    {
        $this->afzender = $afzender;
    }

    /**
     * @return \GemeenteAmsterdam\BrievenhulpBundle\Entity\Tag[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
     * @return boolean
     */
    public function hasTag(Tag $tag)
    {
        return $this->tags->contains($tag);
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        if ($this->hasTag($tag) === false) {
            $this->tags->add($tag);
        }
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        if ($this->hasTag($tag) === true) {
            $this->tags->removeElement($tag);
        }
    }

    public function clearTags()
    {
        $this->tags->clear();
    }
}