<?php

namespace Proximum\Vimeet\Domain\Rooming\Accommodation;

use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Time\MidnightTransformer;

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
        $arrivalAtMidnight = MidnightTransformer::getDateAtMidnight($arrival);
        $departureAtMidnight = MidnightTransformer::getDateAtMidnight($departure);
        $overnightCapacities = $accommodation->getOvernightCapacities();
        $totalStaysByAccommodationPeriod = $this->stayRepository
            ->getTotalStaysByAccommodationPeriod($accommodation)
        ;

        $capacityPerDays = [];

        foreach ($overnightCapacities as $overnightCapacity) {
            $overnightArrivalDate = MidnightTransformer::getDateAtMidnight($overnightCapacity->getDate());

            if ($arrivalAtMidnight <= $overnightArrivalDate
                && $departureAtMidnight > $overnightArrivalDate
            ) {
                $indexDay = $overnightArrivalDate->format('Y-m-d');
                $capacityPerDays[$indexDay] = $overnightCapacity->getCapacity();
            }
        }

        if (empty($capacityPerDays)) {
            return false;
        }

        $period = new \DatePeriod(
            $arrivalAtMidnight,
            new \DateInterval('P1D'),
            $departureAtMidnight
        );

        foreach ($period as $day) {
            if ($departureAtMidnight === $day) {
                break;
            }

            $indexDay = $day->format('Y-m-d');

            if (!isset($capacityPerDays[$indexDay])) {
                return false;
            }
        }

        $totalAssignByDay = [];
        foreach ($totalStaysByAccommodationPeriod as $totalStaysPerPeriod) {
            $period = new \DatePeriod(
                $totalStaysPerPeriod->arrival,
                new \DateInterval('P1D'),
                $totalStaysPerPeriod->departure
            );

            foreach ($period as $day) {
                $midnightDay = MidnightTransformer::getDateAtMidnight($day);

                if ($arrivalAtMidnight <= $midnightDay
                    && $departureAtMidnight > $midnightDay
                ) {
                    $indexDay = $midnightDay->format('Y-m-d');

                    if (isset($totalAssignByDay[$indexDay])) {
                        $totalAssignByDay[$indexDay] += $totalStaysPerPeriod->totalStays;
                    } else {
                        $totalAssignByDay[$indexDay] = $totalStaysPerPeriod->totalStays;
                    }
                }
            }
        }

        foreach ($capacityPerDays as $indexDay => $capacityPerDay) {
            if (isset($totalAssignByDay[$indexDay]) && $totalAssignByDay[$indexDay] >= $capacityPerDay) {
                return false;
            }
        }

        return true;
    }
}
