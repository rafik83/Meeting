<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot\Import;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpotImport
{
    /** @var UploadedFile */
    public $file;

    /** @var string */
    public $charset;
}
