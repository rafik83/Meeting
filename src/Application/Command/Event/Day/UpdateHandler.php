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
            /** @var \DateTimeInterface $eventStartTime */
            $eventStartTime = $day['startTime'];

            /** @var \DateTimeInterface $eventEndTime */
            $eventEndTime = $day['endTime'];

            $this->dayRepository->add(
                new Day(
                    $update->event,
                    $eventStartTime,
                    $eventEndTime
                )
            );
        }
    }
}
