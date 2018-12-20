<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Rooming\Accommodation;

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
