<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
}
