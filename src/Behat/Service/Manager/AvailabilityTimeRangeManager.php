<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class AvailabilityTimeRangeManager
{
    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    public function __construct(AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository)
    {
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
    }

    /**
     * @param Event  $event
     * @param string $name
     * @param string $begin Date of begin in format '2017-10-10 10:00:00.000'
     * @param string $end   Date of end in format '2017-10-10 10:00:00.000'
     *
     * @throws DayNotDefinedException
     *
     * @return AvailabilityTimeRange
     */
    public function create(Event $event, string $name, string $begin, string $end): AvailabilityTimeRange
    {
        $dayCloned = clone $event->getFirstDay()->getDay();
        $dayCloned->setTimeZone(new \DateTimeZone($event->getTimeZone()));

        $beginDate = clone $dayCloned;
        $beginDate->modify(sprintf('%s', $begin));
        $beginDate->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

        $endDate = clone $dayCloned;
        $endDate->modify(sprintf('%s', $end));
        $endDate->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

        $availabilityTimeRange = new AvailabilityTimeRange(
            $event,
            $name,
            $beginDate,
            $endDate
        );

        $this->availabilityTimeRangeRepository->add($availabilityTimeRange);

        return $availabilityTimeRange;
    }
}
