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
     * AgendaParticipantViewQuery constructor.
     *
     * @param Participant $participant
     * @param Event       $event
     * @param Sheet       $sheet
     * @param string      $locale
     */
    public function __construct(Participant $participant, Event $event, Sheet $sheet, $locale)
    {
        $this->event       = $event;
        $this->participant = $participant;
        $this->sheet       = $sheet;
        $this->locale      = $locale;
    }
}
