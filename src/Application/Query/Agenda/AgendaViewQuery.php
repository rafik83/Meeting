<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AgendaViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var null|User
     */
    public $user;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Event       $event
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param string      $locale
     * @param null|User   $user
     */
    public function __construct(Event $event, Sheet $sheet, Participant $participant, $locale, User $user = null)
    {
        $this->event       = $event;
        $this->sheet       = $sheet;
        $this->participant = $participant;
        $this->user        = $user;
        $this->locale      = $locale;
    }
}
