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
use Proximum\Vimeet\Domain\Model\File;

class Import
{
    /** @var File */
    public $file;

    /** @var Event */
    public $event;

    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /**
     * @param File   $file
     * @param Event  $event
     * @param string $emailToNotify
     */
    public function __construct($file, $event, $emailToNotify)
    {
        $this->file          = $file;
        $this->event         = $event;
        $this->emailToNotify = $emailToNotify;
    }
}
