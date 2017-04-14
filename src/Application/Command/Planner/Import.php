<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Import
{
    /** @var int */
    public $fileId;

    /** @var int */
    public $eventId;

    /** @var string */
    public $emailToNotify;

    /**
     * @param int    $fileId
     * @param int    $eventId
     * @param string $emailToNotify
     */
    public function __construct($fileId, $eventId, $emailToNotify)
    {
        $this->fileId        = $fileId;
        $this->eventId       = $eventId;
        $this->emailToNotify = $emailToNotify;
    }
}
