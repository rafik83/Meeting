<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Rooming\Accommodation;

use Proximum\Vimeet\Application\View\Rooming\Accommodation\AccommodationListView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\AccommodationView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\OvernightCapacityView;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;
use Proximum\Vimeet\Domain\Time\MidnightTransformer;

class AccommodationListViewQueryHandler
{
    /** @var AccommodationRepositoryInterface */
    private $accommodationRepository;

    public function __construct(AccommodationRepositoryInterface $accommodationRepository)
    {
        $this->accommodationRepository = $accommodationRepository;
    }

    public function handle(AccommodationListViewQuery $query): AccommodationListView
    {
        $overnight = [];
        $accommodationViews = [];
        $accommodations = $this->accommodationRepository->getByEvent($query->event);

        uasort($accommodations, function (Accommodation $accommodationOne, Accommodation $accommodationTwo) {
            return strcmp($accommodationOne->getTitle(), $accommodationTwo->getTitle());
        });

        foreach ($accommodations as $accommodation) {
            $accommodationView = new AccommodationView(
                $accommodation->getId(),
                $accommodation->getTitle()
            );

            $overnightCapacities = $accommodation->getOvernightCapacities();

            foreach ($overnightCapacities as $overnightCapacity) {
                $midnightDate = MidnightTransformer::getDateAtMidnight($overnightCapacity->getDate());
                $formattedDate = $midnightDate->format('d/m/Y');

                if (!isset($overnight[$formattedDate])) {
                    $overnight[$formattedDate] = $midnightDate;
                }

                $accommodationView->addOvernightCapacityView(
                    $formattedDate,
                    new OvernightCapacityView(
                        $midnightDate,
                        $overnightCapacity->getCapacity()
                    )
                );
            }

            $accommodationViews[] = $accommodationView;
        }

        uasort($overnight, function (\DateTimeInterface $overnightOne, \DateTimeInterface $overnightTwo) {
            return $overnightOne <=> $overnightTwo;
        });

        return new AccommodationListView(
            $accommodationViews,
            $overnight
        );
    }
}
