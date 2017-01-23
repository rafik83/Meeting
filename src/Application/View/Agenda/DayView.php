<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class DayView
{
    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var int
     */
    public $scale;

    /**
     * @var HappeningView[]
     */
    public $happenings;

    /**
     * @var UnavailabilityView[]
     */
    public $unavailabilities;

    /**
     * @var MassUnavailabilityView[]
     */
    public $masses;

    /**
     * @var MeetingView[]
     */
    public $meetings;

    /**
     * @param \DateTimeInterface       $begin
     * @param \DateTimeInterface       $end
     * @param int                      $scale
     * @param HappeningView[]          $happenings
     * @param UnavailabilityView[]     $unavailabilities
     * @param MassUnavailabilityView[] $masses
     * @param MeetingView[]            $meetings
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $scale,
        array $happenings,
        array $unavailabilities,
        array $masses,
        array $meetings
    ) {
        $this->begin            = $begin;
        $this->end              = $end;
        $this->scale            = $scale;
        $this->happenings       = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses           = $masses;
        $this->meetings         = $meetings;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDay()
    {
        return $this->begin;
    }

    /**
     * @return string
     */
    public function getScale()
    {
        return gmdate('H:i', $this->scale * 60);
    }
}
