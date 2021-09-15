<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotViewQueryHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @var SheetView[]
     */
    private $sheets = [];

    /**
     * @var SlotView[]
     */
    private $slots = [];

    /**
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param SpotViewQuery $query
     *
     * @return SpotView[]
     */
    public function handle(SpotViewQuery $query)
    {
        $this->setUp($query);
        $spotViews = [];
        $spots     = $this->spotRepository->getActiveByEvent($query->event);

        foreach ($spots as $spot) {
            $sheetsList = [];

            if ($spot->hasSheets()) {
                foreach ($spot->getSheets() as $sheet) {
                    $sheetView = $this->getSheetById($sheet->getId());

                    if (null !== $sheetView) {
                        $sheetsList[] = $sheetView;
                    }
                }

                if (empty($sheetsList)) {
                    // Skip this spot as it is not a mutalize one
                    // But its sheets are not in the catalog
                    continue;
                }
            }

            $unavailabilities    = $spot->getSpotUnavailabilities();
            $unavailabilityViews = [];

            foreach ($unavailabilities as $unavailability) {
                $slotView = $this->getSlotById($unavailability->getSlot()->getId());

                if (null !== $slotView) {
                    $unavailabilityViews[] = $slotView;
                }
            }

            $spotViews[] = new SpotView(
                $spot->getId(),
                $spot->isVisio(),
                $spot->getReference(),
                $spot->getSeatCapacity(),
                $spot->getMeetingCapacity(),
                $sheetsList,
                $spot->getPriority(),
                $unavailabilityViews
            );
        }

        return $spotViews;
    }

    /**
     * @param SpotViewQuery $query
     */
    private function setUp(SpotViewQuery $query)
    {
        $this->indexById($query);
    }

    /**
     * @param SpotViewQuery $query
     */
    private function indexById(SpotViewQuery $query)
    {
        foreach ($query->sheets as $sheet) {
            $this->sheets[$sheet->id] = $sheet;
        }

        foreach ($query->slots as $slot) {
            $this->slots[$slot->id] = $slot;
        }
    }

    /**
     * @param int $id
     *
     * @return SheetView|null
     */
    private function getSheetById($id)
    {
        if (isset($this->sheets[$id])) {
            return $this->sheets[$id];
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return null|SlotView
     */
    private function getSlotById($id)
    {
        if (isset($this->slots[$id])) {
            return $this->slots[$id];
        }

        return null;
    }
}
