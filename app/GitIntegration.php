<?php
/*
 *  Copyright (C) 2015 X Gemeente
 *                     X Amsterdam
 *                     X Onderzoek, Informatie en Statistiek
 *
 *  This Source Code Form is subject to the terms of the Mozilla Public
 *  License, v. 2.0. If a copy of the MPL was not distributed with this
 *  file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace App;

class GitIntegration
{
    static public function saveGitId()
    {
        $gitRevision = exec('git rev-parse --short HEAD');

        file_put_contents('app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'git.yml', "parameters:\r\n    git_rev: '{$gitRevision}'\r\n");
    }
}