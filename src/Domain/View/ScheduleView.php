<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class ScheduleView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var \DateTime
     */
    public $date;

    /**
     * @var ScheduleSlotView[]
     */
    public $meetings;

    /**
     * @var ScheduleSlotView[]
     */
    public $happenings;

    /**
     * @var ScheduleSlotView[]
     */
    public $unavailabilities;

    /**
     * ScheduleView constructor.
     *
     * @param int       $id
     * @param \DateTimeInterface $date
     */
    public function __construct($id, \DateTimeInterface $date)
    {
        $this->id               = $id;
        $this->date             = $date;
        $this->meetings         = [];
        $this->happenings       = [];
        $this->unavailabilities = [];
    }

    /**
     * @param int              $id
     * @param ScheduleSlotView $scheduleSlotView
     */
    public function addMeeting($id, ScheduleSlotView $scheduleSlotView)
    {
        $this->meetings[$id] = $scheduleSlotView;
    }

    /**
     * @param ScheduleSlotView $scheduleSlotView
     */
    public function addHappening(ScheduleSlotView $scheduleSlotView)
    {
        $this->happenings[] = $scheduleSlotView;
    }

    /**
     * @param ScheduleSlotView $scheduleSlotView
     */
    public function addUnavailability(ScheduleSlotView $scheduleSlotView)
    {
        $this->unavailabilities[] = $scheduleSlotView;
    }
}
