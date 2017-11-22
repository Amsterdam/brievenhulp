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

namespace GemeenteAmsterdam\BrievenhulpBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class UploadedFile extends SymfonyUploadedFile
{
    protected $_test;

    public function __construct($path, $originalName, $mimeType = null, $size = null, $error = null, $test = false)
    {
        parent::__construct($path, $originalName, $mimeType, $size, $error, $test);
        $this->_test = $test;
    }

    public function isValid()
    {
        $isOk = $this->getError() === UPLOAD_ERR_OK;

        return $this->getError() ? $isOk : $isOk;
    }

    public function move($directory, $name = null)
    {
        if ($this->isValid()) {
            if ($this->_test) {
                return parent::move($directory, $name);
            }

            $target = $this->getTargetFile($directory, $name);

            if (!@rename($this->getPathname(), $target)) {
                $error = error_get_last();
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
            }

            @chmod($target, 0666 & ~umask());

            return $target;
        }

        throw new FileException($this->getErrorMessage());
    }
}