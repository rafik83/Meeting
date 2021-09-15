<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Agenda\AbstractTimeEntityView;
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Happening\DayView;
use Proximum\Vimeet\Application\View\Happening\ProgramElementViewInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class DayViewQueryHandler
{
    /** @var HappeningViewQueryHandler */
    private $happeningViewQueryHandler;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var MassUnavailabilityViewQueryHandler */
    private $massUnavailabilityViewQueryHandler;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningViewQueryHandler $happeningViewQueryHandler,
        MassUnavailabilityViewQueryHandler $massUnavailabilityViewQueryHandler
    ) {
        $this->happeningRepository                = $happeningRepository;
        $this->happeningViewQueryHandler          = $happeningViewQueryHandler;
        $this->massUnavailabilityViewQueryHandler = $massUnavailabilityViewQueryHandler;
    }

    public function handle(DayViewQuery $query): DayView
    {
        $happenings = $this->happeningRepository->findByEventAndTypeAndDayAndCategory(
            $query->event,
            $query->sheet->getType(),
            $query->timeRange->getBegin(),
            $query->locale,
            $query->category
        );

        $happeningViews = [];
        $massViews = [];

        foreach ($happenings as $happening) {
            $happeningViews[] = $this->happeningViewQueryHandler->handle(
                new HappeningViewQuery($query->user, $happening, $query->event, $query->locale)
            );
        }

        foreach ($query->masses as $mass) {
            if ($mass->getBegin() >= $query->timeRange->getBegin()
                && $mass->getBegin() <= $query->timeRange->getEnd()
            ) {
                $massViews[] = $this->massUnavailabilityViewQueryHandler->handle(
                    new MassUnavailabilityViewQuery($mass, $query->event, $query->locale)
                );
            }
        }

        return new DayView(
            $query->timeRange->getBegin(),
            $query->timeRange->getEnd(),
            $query->event->getConfiguration()->getScheduleScale(),
            $happeningViews,
            $this->mergeAndSortMassViewsAndHappeningViews($happeningViews, $massViews)
        );
    }

    /**
     * Merge array of MassUnavailabilityView and array of HappeningView
     * Then sort it by begin date, if begin date are equals the one who have the lowest end date rule
     *
     * @param HappeningView[]          $happeningViews
     * @param MassUnavailabilityView[] $massUnavailabilityView
     *
     * @return ProgramElementViewInterface[]
     */
    private function mergeAndSortMassViewsAndHappeningViews(array $happeningViews, array $massUnavailabilityView): array
    {
        $programElementViews = array_merge($happeningViews, $massUnavailabilityView);

        usort($programElementViews, static function (AbstractTimeEntityView $first, AbstractTimeEntityView $second) {
            if ($first->begin->getTimestamp() === $second->begin->getTimestamp()) {
                return 0;
            }

            return $first->begin < $second->begin ? -1 : 1;
        });

        return $programElementViews;
    }
}
