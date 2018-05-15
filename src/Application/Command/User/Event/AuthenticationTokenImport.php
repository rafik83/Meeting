<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Application\Command\Command;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthenticationTokenImport implements Command
{
    /** @var UploadedFile */
    public $file;
}
