<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

        if ($event->getFirstDay()->getStartTime() < $this->dateTime
            && $event->getLastDay()->getEndTime() > $this->dateTime
        ) {
            return true;
        }

        return false;
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
