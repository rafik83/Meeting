<?php

namespace Proximum\Vimeet\Application\Command\AvailabilityTimeRange;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $name;

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    public function __construct(Event $event)
    {
        $this->event = $event;

        if ($this->event->hasDay()) {
            $firstDay = $this->event->getFirstDay();

            $this->begin = $firstDay->getBegin();
            $this->end = $firstDay->getEnd();
        }
    }

    public function checkBeginAndEndAreBetweenEventDates(): bool
    {
        if ($this->event->hasDay()) {
            try {
                if ($this->begin < $this->event->getFirstDay()->getBegin()) {
                    return false;
                }

                if ($this->end > $this->event->getLastDay()->getEnd()) {
                    return false;
                }

                return true;
            } catch (DayNotDefinedException $exception) {
                return false;
            }
        }

        return false;
    }
}
