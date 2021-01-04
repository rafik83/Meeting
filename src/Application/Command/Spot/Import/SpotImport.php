<?php

namespace Proximum\Vimeet\Application\Command\Spot\Import;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpotImport
{
    /** @var UploadedFile */
    public $file;

    /** @var string */
    public $charset;
}
