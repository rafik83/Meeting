<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Import implements Command
{
    /** @var Type */
    public $type;

    /** @var UploadedFile */
    public $file;

    /** @var string */
    public $charset;

    /** @var bool */
    public $allowMultiSheet;
}
