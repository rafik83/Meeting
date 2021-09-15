<?php

namespace Proximum\Vimeet\Application\Command\AvailabilityTimeRange;

use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class CreateHandler
{
    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    public function __construct(AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository)
    {
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
    }

    public function handle(Create $create): void
    {
        $this->availabilityTimeRangeRepository->add(new AvailabilityTimeRange(
            $create->event,
            $create->name,
            $create->begin,
            $create->end
        ));
    }
}
