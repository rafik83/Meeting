<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class ExportJobCreator
{
    /** @var Event */
    public $event;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    /** @var bool */
    public $lockMeetingRequest = false;

    /** @var string one of SolutionType constants */
    public $solutionType;

    /**
     * ExportJobCreator constructor.
     *
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     */
    public function __construct(Event $event, Admin $admin, $locale)
    {
        $this->event  = $event;
        $this->admin  = $admin;
        $this->locale = $locale;
    }
}
