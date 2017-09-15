<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

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
    public  $emailToNotify;

    /** @var bool */
    public $isModeAuto;

    /**
     * @param int    $eventId
     * @param string $locale
     * @param string $emailToNotify
     * @param bool   $lockMeetingRequest
     * @param string $solutionType
     * @param bool   $isModeAuto
     */
    public function __construct(
        int $eventId,
        string $locale,
        string $emailToNotify,
        bool $lockMeetingRequest,
        string $solutionType,
        bool $isModeAuto
    ) {
        if (!in_array($solutionType, ExportSolutionType::getExportSolutionTypes(), true)) {
            throw new \InvalidArgumentException('solutionType must be one of ExportSolutionType');
        }

        $this->eventId            = $eventId;
        $this->locale             = $locale;
        $this->emailToNotify      = $emailToNotify;
        $this->lockMeetingRequest = $lockMeetingRequest;
        $this->solutionType       = $solutionType;
        $this->isModeAuto         = $isModeAuto;
    }
}
