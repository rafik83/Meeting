<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeOverlap;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class HasStayForPeriod
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(StayRepositoryInterface $stayRepository)
    {
        $this->stayRepository = $stayRepository;
    }

    public function isSatisfiedBy(Event $event, User $user, \DateTimeInterface $arrival, \DateTimeInterface $departure): bool
    {
        $periodAtMidnight = new TimeRangeView(
            $this->getDateAtMidnight($arrival),
            $this->getDateAtMidnight($departure)
        );

        $timeRanges = $this->stayRepository->getTimeRangeViewsByUserAndEvent($user, $event);

        foreach ($timeRanges as $timeRange) {
            $timeRangeAtMidnight = new TimeRangeView(
                $this->getDateAtMidnight($timeRange->getBegin()),
                $this->getDateAtMidnight($timeRange->getEnd())
            );

            if (TimeOverlap::overlap($timeRangeAtMidnight, $periodAtMidnight)) {
                return true;
            }
        }

        return false;
    }

    private function getDateAtMidnight(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTime(0, 0, 0, 0)
        ;
    }
}
