<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Rooming\Accommodation;

use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class HasRemainingOvernight
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(StayRepositoryInterface $stayRepository)
    {
        $this->stayRepository = $stayRepository;
    }

    public function isSatisfiedBy(
        Accommodation $accommodation,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure
    ): bool {
        $arrivalAtMidnight = $this->getDateAtMidnight($arrival);
        $departureAtMidnight = $this->getDateAtMidnight($departure);
        $overnightCapacities = $accommodation->getOvernightCapacities();
        $totalStaysByAccommodationPeriod = $this->stayRepository
            ->getTotalStaysByAccommodationPeriod($accommodation)
        ;

        $capacityPerDays = [];

        foreach ($overnightCapacities as $overnightCapacity) {
            $overnightArrivalDate = $this->getDateAtMidnight($overnightCapacity->getDate());

            if ($arrivalAtMidnight <= $overnightArrivalDate
                && $departureAtMidnight > $overnightArrivalDate
            ) {
                $capacityPerDays[$overnightArrivalDate->format('Y-m-d')] = $overnightCapacity->getCapacity();
            }
        }

        if (empty($capacityPerDays)) {
            return false;
        }

        $totalAssignByDay = [];
        foreach ($totalStaysByAccommodationPeriod as $totalStaysPerPeriod) {
            $period = new \DatePeriod(
                $totalStaysPerPeriod->arrival,
                new \DateInterval('P1D'),
                $totalStaysPerPeriod->departure
            );

            foreach ($period as $day) {
                $midnightDay = $this->getDateAtMidnight($day);

                if ($arrivalAtMidnight <= $midnightDay
                    && $departureAtMidnight > $midnightDay
                ) {
                    if (isset($totalAssignByDay[$midnightDay->format('Y-m-d')])) {
                        $totalAssignByDay[$midnightDay->format('Y-m-d')] += $totalStaysPerPeriod->totalStays;
                    } else {
                        $totalAssignByDay[$midnightDay->format('Y-m-d')] = $totalStaysPerPeriod->totalStays;
                    }
                }
            }
        }

        foreach ($capacityPerDays as $key => $capacityPerDay) {
            if (isset($totalAssignByDay[$key]) && $totalAssignByDay[$key] >= $capacityPerDay) {
                return false;
            }
        }

        return true;
    }

    private function getDateAtMidnight(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        return (new \DateTime())->setTimestamp($dateTime->getTimestamp())->setTime(0, 0, 0, 0);
    }
}
