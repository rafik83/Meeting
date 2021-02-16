<?php

namespace Proximum\Vimeet\Application\Query\AvailabilityTimeRange;

use Proximum\Vimeet\Application\View\AvailabilityTimeRange\ListView;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class ListViewQueryHandler
{
    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    /** @var AvailabilityTimeRangeViewQueryHandler */
    private $availabilityTimeRangeViewQueryHandler;

    public function __construct(
        AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository,
        AvailabilityTimeRangeViewQueryHandler $availabilityTimeRangeViewQueryHandler
    ) {
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
        $this->availabilityTimeRangeViewQueryHandler = $availabilityTimeRangeViewQueryHandler;
    }

    public function handle(ListViewQuery $query): ListView
    {
        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($query->event);
        $availabilityTimeRangeViews = [];

        foreach ($availabilityTimeRanges as $availabilityTimeRange) {
            $availabilityTimeRangeViews[] = $this->availabilityTimeRangeViewQueryHandler->handle(
                new AvailabilityTimeRangeViewQuery($availabilityTimeRange)
            );
        }

        return new ListView($availabilityTimeRangeViews);
    }
}
