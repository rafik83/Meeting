<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Create
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
     * @var array
     */
    public $participants;

    /**
     * @var Event\Day|null
     */
    public $day;

    /**
     * @var array
     */
    public $time;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     */
    public function __construct(Event $event, Sheet $sheet, User $user, $locale)
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
    }
}
