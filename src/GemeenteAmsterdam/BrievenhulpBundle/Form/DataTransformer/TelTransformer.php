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

namespace GemeenteAmsterdam\BrievenhulpBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class TelTransformer implements DataTransformerInterface
{
    public function transform($number)
    {
        if (substr($number, 0, 3) === '+31') {
            return '0' . substr($number, 3);
        }
        return $number;
    }

    public function reverseTransform($plain)
    {
        $tel = str_replace([' ', '-', '(', ')'], '', $plain);

        $replaces = [
            '01' => '+311',
            '02' => '+312',
            '03' => '+313',
            '04' => '+314',
            '05' => '+315',
            '06' => '+316',
            '07' => '+317',
            '08' => '+318',
            '09' => '+319',
            '+316' => '+316',
            '+3106' => '+316',
            '003106' => '+316',
        ];
        foreach ($replaces as $find => $replace) {
            if (substr($tel, 0, strlen($find)) === $find) {
                $tel = $replace . substr($tel, strlen($find));
            }
        }

        return $tel;
    }
}