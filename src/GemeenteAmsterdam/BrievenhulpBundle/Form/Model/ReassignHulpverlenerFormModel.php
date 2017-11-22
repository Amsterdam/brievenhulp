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

class ReassignHulpverlenerFormModel
{
    /**
     * @var string
     * @Assert\Length(min=1, max=1024)
     */
    public $bericht;

    /**
     * @var Hulpverlener
     * @Assert\NotBlank
     */
    public $hulpverlener;
}