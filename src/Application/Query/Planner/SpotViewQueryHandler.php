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
            $priority   = 10;
            $sheetsList = [];

            // Will have to be rewritten when priority will be placed on spot
            if ($spot->hasSheets()) {
                $priority = 8;

                foreach ($spot->getSheets() as $sheet) {
                    $sheetsList[] = $this->getSheetById($sheet->getId());
                }
            }

            $spotViews[] = new SpotView(
                $spot->getId(),
                $spot->getReference(),
                $spot->getSeatCapacity(),
                $spot->getMeetingCapacity(),
                $sheetsList,
                $priority,
                [] // Coming spot unavailability
            );
        }

        return $spotViews;
    }

    /**
     * @param SpotViewQuery $query
     */
    private function setUp(SpotViewQuery $query)
    {
        $this->indexSheetsById($query);
    }

    /**
     * @param SpotViewQuery $query
     */
    private function indexSheetsById(SpotViewQuery $query)
    {
        foreach ($query->sheets as $sheet) {
            $this->sheets[$sheet->id] = $sheet;
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
}
