<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Create implements Command
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * @var Event\Day
     */
    public $day;

    /**
     * @var array
     */
    public $time;

    /**
     * @var string|null
     */
    public $message;

    /**
     * @var string
     */
    public $locale;

    /** @var string */
    public $timezone;

    public function __construct(Event $event, Sheet $sheet, User $user, string $locale, string $timezone)
    {
        $this->event  = $event;
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->day    = $event->getFirstDay();

        $participant = $sheet->getUserParticipant($user);

        if (null !== $participant) {
            $this->participants[] = $participant;
        }

        $this->locale = $locale;
        $this->timezone = $timezone;
    }

    /**
     * @return bool
     */
    public function validateDate()
    {
        if (!isset($this->time['begin']['hour'])
            || !isset($this->time['begin']['minute'])
            || !isset($this->time['end']['hour'])
            || !isset($this->time['end']['minute'])
        ) {
            return false;
        }

        if ($this->time['begin']['hour'] < $this->time['end']['hour']) {
            return true;
        }

        if ($this->time['begin']['hour'] === $this->time['end']['hour']
            && $this->time['begin']['minute'] < $this->time['end']['minute']
        ) {
            return true;
        }

        return false;
    }
}
