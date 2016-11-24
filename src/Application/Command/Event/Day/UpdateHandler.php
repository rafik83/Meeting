<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Day;

use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class UpdateHandler
{
    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @param DayRepositoryInterface $dayRepository
     */
    public function __construct(DayRepositoryInterface $dayRepository)
    {
        $this->dayRepository = $dayRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $this->dayRepository->removeFromEvent($update->event);

        foreach ($update->days as $day) {
            /** @var \DateTimeInterface $eventDay */
            $eventDay = $day['day'];
            // set the time to 0 to avoid problem with timezone
            date_time_set($eventDay, 0, 0);

            /** @var \DateTimeInterface $eventStartTime */
            $eventStartTime = $day['startTime'];
            // set the date to be the same as the $day['day'] to get the info if needed
            date_date_set($eventStartTime, $eventDay->format('Y'), $eventDay->format('n'), $eventDay->format('d'));

            /** @var \DateTimeInterface $eventEndTime */
            $eventEndTime = $day['endTime'];
            // set the date to be the same as the $day['day'] to get the info if needed
            date_date_set($eventEndTime, $eventDay->format('Y'), $eventDay->format('n'), $eventDay->format('d'));

            $this->dayRepository->add(
                new Day(
                    $update->event,
                    $eventDay,
                    $eventStartTime,
                    $eventEndTime
                )
            );
        }
    }
}
