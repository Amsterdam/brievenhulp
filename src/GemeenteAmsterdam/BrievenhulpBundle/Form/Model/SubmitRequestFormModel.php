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

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @Assert\GroupSequenceProvider
 */
class SubmitRequestFormModel implements GroupSequenceProviderInterface
{
    /**
     * @var UploadedFile
     * @Assert\NotBlank
     * @Assert\File(maxSize="10Mi", mimeTypes={"image/png", "image/jpg", "image/jpeg", "application/pdf"})
     */
    public $foto;

    /**
     * @var string
     * @Assert\Length(min=0, max=2048)
     */
    public $vraag;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Choice(choices={"email", "sms", "telefoon"})
     */
    public $methode;

    /**
     * @var string
     * @Assert\NotBlank(groups="methode-email")
     * @Assert\Length(min=1, max=200, groups="methode-email")
     * @Assert\Email(groups="methode-email")
     */
    public $emailadres;

    /**
     * @var string
     * @Assert\NotBlank(groups={"methode-sms", "methode-telefoon"})
     * @Assert\Length(min=1,max=12, groups={"methode-sms", "methode-telefoon"})
     * @Assert\Regex("/^\+[0-9]{10}/")
     */
    public $telefoon;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Choice(choices={"nl", "en"})
     */
    public $taal;

    static public function getMethodes()
    {
        return [
            'email' => 'E-mail',
            'sms' => 'SMS',
            'telefoon' => 'Telefoon'
        ];
    }

    static public function getTalen()
    {
        return [
            'nl' => 'Nederlands',
            'en' => 'Engels',
        ];
    }

    /**
     * @return string[]
     */
    public function getGroupSequence()
    {
        $groups = ['SubmitRequestFormModel'];
        if ($this->methode === 'email')
            $groups[] = 'methode-email';
        if ($this->methode === 'sms')
            $groups[] = 'methode-sms';
        if ($this->methode === 'telefoon')
            $groups[] = 'methode-telefoon';
        return $groups;
    }
}