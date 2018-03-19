<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\AvailabilityTimeRange;

use Proximum\Vimeet\Application\View\AvailabilityTimeRange\AvailabilityTimeRangeView;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\ListView;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class ListViewQueryHandler
{
    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    public function __construct(AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository)
    {
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
    }

    public function handle(ListViewQuery $query): ListView
    {
        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($query->event);
        $availabilityTimeRangeViews = [];

        foreach ($availabilityTimeRanges as $availabilityTimeRange) {
            $availabilityTimeRangeViews[] = new AvailabilityTimeRangeView(
                $availabilityTimeRange->getName(),
                $availabilityTimeRange->getBegin(),
                $availabilityTimeRange->getEnd()
            );
        }

        return new ListView($availabilityTimeRangeViews);
    }
}
