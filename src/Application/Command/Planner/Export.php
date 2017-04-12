<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

class Export
{
    /** @var bool */
    public $lockMeetingRequest;

    /** @var string one of SolutionType constants */
    public $solutionType;

    /** @var int */
    public $eventId;

    /** @var string */
    public $locale;

    /** @var string */
    public $emailToNotify;

    /**
     * @param int $eventId
     * @param string $locale
     * @param string $emailToNotify
     * @param bool   $lockMeetingRequest
     * @param string $solutionType
     */
    public function __construct($eventId, $locale, $emailToNotify, $lockMeetingRequest, $solutionType)
    {
        $this->eventId            = $eventId;
        $this->locale             = $locale;
        $this->emailToNotify      = $emailToNotify;
        $this->lockMeetingRequest = $lockMeetingRequest;
        $this->solutionType       = $solutionType;
    }
}
