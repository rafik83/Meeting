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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @var array
     */
    public $happeningParticipations;

    /**
     * @var array
     */
    public $unavailabilites;

    /**
     * @var array
     */
    public $masses;

    /**
     * @var array
     */
    public $meetings;

    /**
     * AgendaParticipantViewQuery constructor.
     *
     * @param Participant $participant
     * @param Event       $event
     * @param Sheet       $sheet
     * @param string      $locale
     * @param array       $happeningParticipations
     * @param array       $unavailabilites
     * @param array       $masses
     * @param array       $meetings
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
