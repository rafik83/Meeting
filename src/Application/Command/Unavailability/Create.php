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
use Proximum\Vimeet\Domain\Time\DaysHelper;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var Participant[] */
    public $participants;

    /** @var TimeRangeView */
    public $day;

    /** @var array */
    public $time;

    /** @var string|null */
    public $message;

    /** @var string */
    public $locale;

    /** @var string */
    public $timezone;

    public function __construct(Event $event, Sheet $sheet, User $user, string $locale, string $timezone)
    {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->locale = $locale;
        $participant = $sheet->getUserParticipant($user);

        if (null !== $participant) {
            $this->participants[] = $participant;
        }

        $this->locale = $locale;
        $this->timezone = $timezone;

        $firstDay = $event->getFirstDay();
        $this->day = new TimeRangeView(
            DaysHelper::cloneDateTime($firstDay->getStartTime(), $timezone),
            DaysHelper::cloneDateTime($firstDay->getEndTime(), $timezone)
        );
    }

    public function validateDate(): bool
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
