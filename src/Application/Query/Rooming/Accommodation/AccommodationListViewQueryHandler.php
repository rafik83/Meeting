<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Accommodation;

use Proximum\Vimeet\Application\View\Rooming\Accommodation\AccommodationListView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\AccommodationView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\OvernightCapacityView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\OvernightTotalView;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Time\MidnightTransformer;

class AccommodationListViewQueryHandler
{
    /** @var AccommodationRepositoryInterface */
    private $accommodationRepository;

    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(
        AccommodationRepositoryInterface $accommodationRepository,
        StayRepositoryInterface $stayRepository
    ) {
        $this->accommodationRepository = $accommodationRepository;
        $this->stayRepository = $stayRepository;
    }

    public function handle(AccommodationListViewQuery $query): AccommodationListView
    {
        $overnightTotalViews = [];

        /** @var AccommodationView[] $accommodationViews */
        $accommodationViews = [];
        $accommodations = $this->accommodationRepository->getByEvent($query->event);
        $stays = $this->stayRepository->getAccommodationStaysByEvent($query->event);

        $this->sortAccommodationByAlphabeticalOrder($accommodations);

        foreach ($accommodations as $accommodation) {
            $accommodationView = new AccommodationView(
                $accommodation->getId(),
                $accommodation->getTitle()
            );

            $overnightCapacities = $accommodation->getOvernightCapacities();

            foreach ($overnightCapacities as $overnightCapacity) {
                $midnightDate = MidnightTransformer::getDateAtMidnight($overnightCapacity->getDate());
                $formattedDate = $midnightDate->format('d/m/Y');

                if (!isset($overnightTotalViews[$formattedDate])) {
                    $overnightTotalViews[$formattedDate] = new OvernightTotalView(
                        $formattedDate,
                        $midnightDate
                    );
                }

                $accommodationView->addOvernightCapacityView(
                    $formattedDate,
                    new OvernightCapacityView(
                        $midnightDate,
                        $overnightCapacity->getCapacity()
                    )
                );

                $overnightTotalViews[$formattedDate]->addToTotal($overnightCapacity->getCapacity());
            }

            $accommodationViews[$accommodation->getId()] = $accommodationView;
        }

        $this->handleRemainingWithStays($stays, $accommodationViews, $overnightTotalViews);

        $this->sortOvernightTotalByDate($overnightTotalViews);

        return new AccommodationListView(
            $accommodationViews,
            $overnightTotalViews
        );
    }

    private function sortAccommodationByAlphabeticalOrder(array &$accommodations): void
    {
        uasort($accommodations, function (Accommodation $accommodationOne, Accommodation $accommodationTwo) {
            return strcmp($accommodationOne->getTitle(), $accommodationTwo->getTitle());
        });
    }

    private function sortOvernightTotalByDate(array &$overnightTotalViews): void
    {
        uasort($overnightTotalViews, function (OvernightTotalView $overnightOne, OvernightTotalView $overnightTwo) {
            return $overnightOne->date <=> $overnightTwo->date;
        });
    }

    private function handleRemainingWithStays(array $stays, array &$accommodationViews, array &$overnight): void
    {
        foreach ($stays as $stay) {
            if (!isset($accommodationViews[$stay->accommodationId])) {
                continue;
            }

            $accommodationView = $accommodationViews[$stay->accommodationId];
            $arrivalAtMidnight = MidnightTransformer::getDateAtMidnight($stay->arrival);
            $departureAtMidnight = MidnightTransformer::getDateAtMidnight($stay->departure);

            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod(
                $arrivalAtMidnight,
                $interval,
                $departureAtMidnight
            );

            foreach ($period as $day) {
                $formattedDate = $day->format('d/m/Y');

                if (!isset($accommodationView->overnightCapacityViews[$formattedDate])) {
                    continue;
                }

                $accommodationView->overnightCapacityViews[$formattedDate]->remaining--;
                $overnight[$formattedDate]->decreaseRemaining();
            }
        }
    }
}
