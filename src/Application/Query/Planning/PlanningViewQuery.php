<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class PlanningViewQuery
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
     * @var string
     */
    public $locale;

    /**
     * @param Event       $event
     * @param Participant $participant
     * @param string      $locale
     */
    public function __construct(Event $event, Participant $participant, $locale)
    {
        $this->event       = $event;
        $this->participant = $participant;
        $this->locale      = $locale;
    }
}
