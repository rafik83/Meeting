<?php

namespace Proximum\Vimeet\Application\Command\Spot\Import;

use Proximum\Vimeet\Application\Command\Command;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpotImport implements Command
{
    /** @var UploadedFile */
    public $file;

    /** @var string */
    public $charset;
}
