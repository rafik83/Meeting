<?php

namespace Proximum\Vimeet\Domain\Event\Day;

use Proximum\Vimeet\Domain\Model\Event;

class EventOver
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function isEventOver(Event $event): bool
    {
        if (!$event->hasDay()) {
            return false;
        }

        $lastDay = $event->getLastDay();

        return $this->dateTime > $lastDay->getEndTime();
    }
}
