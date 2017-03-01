<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Day;

use DateTimeZone;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Update
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $days;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->days  = [];

        foreach ($event->getDays() as $day) {
            $this->days[] = [
                'startTime' => $day->getStartTime(),
                'endTime'   => $day->getEndTime(),
            ];
        }
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateSameDay(ExecutionContextInterface $context)
    {
        foreach ($this->days as $day) {
            $startTime = new \DateTime(
                $day['startTime']->format('Y-m-d'),
                new DateTimeZone($this->event->getTimeZone())
            );

            $endTime = new \DateTime(
                $day['endTime']->format('Y-m-d'),
                new DateTimeZone($this->event->getTimeZone())
            );

            if ($startTime === $endTime) {
                $context->buildViolation('validators.schedule_day.shouldBeTheSameDay')
                    ->atPath('days')
                    ->addViolation();
            }
        }
    }
}
