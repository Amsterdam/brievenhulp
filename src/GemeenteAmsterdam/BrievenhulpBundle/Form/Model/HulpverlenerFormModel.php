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
use Rollerworks\Bundle\PasswordStrengthBundle\Validator\Constraints as RollerworksPassword;

class HulpverlenerFormModel
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(min=1, max=255)
     */
    public $naam;

    /**
     * @var string
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter=false, minLength=8)
     */
    public $password;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(min=1, max=255)
     */
    public $email;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Regex("/^\+[0-9]{11}$/")
     */
    public $telefoon;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(min=1, max=255)
     */
    public $organisatie;

    /**
     * @var array
     * @Assert\Choice(callback={"GemeenteAmsterdam\BrievenhulpBundle\Form\Type\HulpverlenerFormType", "getBeschikbaarheidOptions"}, multiple=true)
     */
    public $beschikbaarheid;

    /**
     * @var string
     * @Assert\NotBlank
     */
    public $role;

    /**
     * @var string
     */
    public $smssjabloon;

    /**
     * @var boolean
     */
    public $isActive;

    /**
     * @var boolean
     * @Assert\Type("boolean")
     */
    public $updatesPerSms;
}