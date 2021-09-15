<?php

namespace Proximum\Vimeet\Domain\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Time\MidnightTransformer;
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
            MidnightTransformer::getDateAtMidnight($arrival),
            MidnightTransformer::getDateAtMidnight($departure)
        );

        $timeRanges = $this->stayRepository->getTimeRangeViewsByUserAndEvent($user, $event);

        foreach ($timeRanges as $timeRange) {
            $timeRangeAtMidnight = new TimeRangeView(
                MidnightTransformer::getDateAtMidnight($timeRange->getBegin()),
                MidnightTransformer::getDateAtMidnight($timeRange->getEnd())
            );

            if (TimeOverlap::overlap($timeRangeAtMidnight, $periodAtMidnight)) {
                return true;
            }
        }

        return false;
    }
}
