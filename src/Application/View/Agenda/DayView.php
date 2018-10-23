<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;

class DayView
{
    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var int */
    public $scale;

    /** @var HappeningView[] */
    public $happenings;

    /** @var UnavailabilityView[] */
    public $unavailabilities;

    /** @var MassUnavailabilityView[] */
    public $masses;

    /** @var MeetingView[] */
    public $meetings;

    /** @var CancelAttendanceUnavailabilityView|null */
    public $cancelAttendanceUnavailabilityView;

    /**
     * @var AvailableSlotView[]
     */
    public $availableSlotViews;

    /**
     * @param \DateTimeInterface                      $begin
     * @param \DateTimeInterface                      $end
     * @param int                                     $scale
     * @param HappeningView[]                         $happenings
     * @param UnavailabilityView[]                    $unavailabilities
     * @param MassUnavailabilityView[]                $masses
     * @param MeetingView[]                           $meetings
     * @param array                                   $availableSlotViews
     * @param CancelAttendanceUnavailabilityView|null $cancelAttendanceUnavailabilityView
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $scale,
        array $happenings,
        array $unavailabilities,
        array $masses,
        array $meetings,
        array $availableSlotViews,
        CancelAttendanceUnavailabilityView $cancelAttendanceUnavailabilityView = null
    ) {
        $this->begin                              = $begin;
        $this->end                                = $end;
        $this->scale                              = $scale;
        $this->happenings                         = $happenings;
        $this->unavailabilities                   = $unavailabilities;
        $this->masses                             = $masses;
        $this->meetings                           = $meetings;
        $this->cancelAttendanceUnavailabilityView = $cancelAttendanceUnavailabilityView;
        $this->availableSlotViews = $availableSlotViews;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDay(): \DateTimeInterface
    {
        return $this->begin;
    }

    /**
     * @return string
     */
    public function getScale(): string
    {
        return gmdate('H:i', $this->scale * 60);
    }

    /**
     * @return array
     */
    public function getTimeEntities(): array
    {
        return array_merge(
            $this->happenings,
            $this->unavailabilities,
            $this->masses,
            $this->meetings
        );
    }

    /**
     * @return bool
     */
    public function isSheetAttendingTheEvent(): bool
    {
        return null === $this->cancelAttendanceUnavailabilityView;
    }

    public function isFullUnavailable(): bool
    {
        return false;
    }
}
