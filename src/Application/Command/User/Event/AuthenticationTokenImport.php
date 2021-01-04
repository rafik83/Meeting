<?php

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Application\Command\Command;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthenticationTokenImport implements Command
{
    /** @var UploadedFile */
    public $file;
}
