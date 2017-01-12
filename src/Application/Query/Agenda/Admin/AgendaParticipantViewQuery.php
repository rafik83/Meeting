<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;

class AgendaParticipantViewQuery
{
    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var HappeningParticipation[]
     */
    public $happeningParticipations;

    /**
     * @var Unavailability[]
     */
    public $unavailabilites;

    /**
     * @var Unavailability\Mass[]
     */
    public $masses;

    /**
     * @var Meeting[]
     */
    public $meetings;

    /**
     * AgendaParticipantViewQuery constructor.
     *
     * @param Participant              $participant
     * @param Event                    $event
     * @param Sheet                    $sheet
     * @param string                   $locale
     * @param HappeningParticipation[] $happeningParticipations
     * @param Unavailability[]         $unavailabilites
     * @param Unavailability\Mass[]    $masses
     * @param Meeting[]                $meetings
     */
    public function __construct(
        Participant $participant,
        Event $event,
        Sheet $sheet,
        $locale,
        array $happeningParticipations,
        array $unavailabilites,
        array $masses,
        array $meetings
    ) {
        $this->event                   = $event;
        $this->participant             = $participant;
        $this->sheet                   = $sheet;
        $this->locale                  = $locale;
        $this->happeningParticipations = $happeningParticipations;
        $this->unavailabilites         = $unavailabilites;
        $this->masses                  = $masses;
        $this->meetings                = $meetings;
    }
}
