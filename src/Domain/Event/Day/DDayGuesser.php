<?php

namespace Proximum\Vimeet\Domain\Event\Day;

use Proximum\Vimeet\Domain\Model\Event;

class DDayGuesser
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var bool */
    private $featureDDayEnabled;

    /**
     * @param \DateTimeInterface $dateTime
     * @param bool               $featureDDayEnabled
     */
    public function __construct(\DateTimeInterface $dateTime, bool $featureDDayEnabled = false)
    {
        $this->dateTime           = $dateTime;
        $this->featureDDayEnabled = $featureDDayEnabled;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function isItDDay(Event $event): bool
    {
        if (!$event->hasDay()) {
            return false;
        }

        return $event->getFirstDay()->getStartTime() < $this->dateTime
            && $event->getLastDay()->getEndTime() > $this->dateTime;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function isItDDayAndFeatureEnabled(Event $event): bool
    {
        return $this->isItDDay($event) && $this->featureDDayEnabled;
    }
}
