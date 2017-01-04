<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class SlotViewQuery
{
    /**
     * @var Day
     */
    public $day;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var HappeningParticipation[]
     */
    public $happenings;

    /**
     * @var Unavailability[]
     */
    public $unavailabilities;

    /**
     * @var Mass[]
     */
    public $masses;

    /**
     * @var Meeting[]
     */
    public $meetings;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * SlotViewQuery constructor.
     *
     * @param Event       $event
     * @param Day         $day
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param array       $happenings
     * @param array       $unavailabilities
     * @param array       $masses
     * @param array       $meetings
     */
    public function __construct(
        Event $event,
        Day $day,
        Sheet $sheet,
        Participant $participant,
        array $happenings,
        array $unavailabilities,
        array $masses,
        array $meetings
    ) {
        $this->day              = $day;
        $this->event            = $event;
        $this->happenings       = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses           = $masses;
        $this->meetings         = $meetings;
        $this->sheet            = $sheet;
        $this->participant      = $participant;
    }
}
