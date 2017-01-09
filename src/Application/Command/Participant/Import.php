<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Import
{
    const PARTICIPANT_IMPORT_FILE = 'participant_import_file';

    /**
     * @var Type
     */
    public $type;

    /**
     * @var UploadedFile
     */
    public $file;
}
