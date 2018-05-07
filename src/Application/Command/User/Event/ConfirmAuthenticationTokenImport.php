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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class ConfirmAuthenticationTokenImport implements Command
{
    /** @var Event */
    public $event;

    /** @var File */
    public $importedFile;

    public function __construct(Event $event, File $importedFile)
    {
        $this->event = $event;
        $this->importedFile = $importedFile;
    }
}
