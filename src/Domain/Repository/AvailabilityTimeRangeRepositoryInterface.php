<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;

interface AvailabilityTimeRangeRepositoryInterface
{
    /**
     * @param AvailabilityTimeRange $availabilityTimeRange
     */
    public function add(AvailabilityTimeRange $availabilityTimeRange): void;

    /**
     * @param Event $event
     *
     * @return AvailabilityTimeRange[]
     */
    public function findByEvent(Event $event): array;

    public function hasByEvent(Event $event): bool;
}
