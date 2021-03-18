<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Accommodation;

use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasRemainingOvernight;

class AccommodationListByPeriodQueryHandler
{
    /** @var AccommodationRepositoryInterface */
    private $accommodationRepository;

    /** @var HasRemainingOvernight */
    private $hasRemainingOvernight;

    public function __construct(
        AccommodationRepositoryInterface $accommodationRepository,
        HasRemainingOvernight $hasRemainingOvernight
    ) {
        $this->accommodationRepository = $accommodationRepository;
        $this->hasRemainingOvernight = $hasRemainingOvernight;
    }

    /**
     * @param AccommodationListByPeriodQuery $query
     *
     * @return Accommodation[]
     */
    public function handle(AccommodationListByPeriodQuery $query): array
    {
        $accommodationsWithRemainingOvernight = [];
        $accommodations = $this->accommodationRepository->getByEvent($query->event);

        foreach ($accommodations as $accommodation) {
            if ($this->hasRemainingOvernight->isSatisfiedBy($accommodation, $query->arrival, $query->departure)) {
                $accommodationsWithRemainingOvernight[] = $accommodation;
            }
        }

        return $accommodationsWithRemainingOvernight;
    }
}
