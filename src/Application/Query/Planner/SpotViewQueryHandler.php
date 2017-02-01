<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner\SheetNotFoundException;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
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
                    $sheetsList[] = $this->getSheetById($sheet->getId());
                }
            }

            $unavailabilities    = $spot->getSpotUnavailabilities();
            $unavailabilityViews = [];

            foreach ($unavailabilities as $unavailability) {
                $slotView = $this->getSlotById($unavailability->getSlot()->getId());

                if ($slotView !== null) {
                    $unavailabilityViews[] = $slotView;
                }
            }

            $spotViews[] = new SpotView(
                $spot->getId(),
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
     * @return SheetView
     *
     * @throws SheetNotFoundException
     */
    private function getSheetById($id)
    {
        if (isset($this->sheets[$id])) {
            return $this->sheets[$id];
        }

        throw new SheetNotFoundException(sprintf('Sheet of id %s was not found', $id));
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
