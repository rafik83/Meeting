<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AgendaViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $userViewing;

    /** @var string */
    public $locale;

    /** @var bool */
    public $allSheet;

    public function __construct(
        Event $event,
        Sheet $sheet,
        Participant $participant,
        string $locale,
        User $userViewing,
        bool $allSheet = false
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->participant = $participant;
        $this->userViewing = $userViewing;
        $this->locale = $locale;
        $this->allSheet = $allSheet;
    }
}
