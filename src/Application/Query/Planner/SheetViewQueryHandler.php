<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetViewQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var TypeView[]
     */
    private $types = [];

    /**
     * @var IndicatorCalculator
     */
    private $indicatorCalculator;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param IndicatorCalculator      $indicatorCalculator
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        IndicatorCalculator $indicatorCalculator
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->indicatorCalculator = $indicatorCalculator;
    }

    /**
     * @param SheetViewQuery $query
     *
     * @return SheetView[]
     */
    public function handle(SheetViewQuery $query)
    {
        $this->orderTypeById($query);
        $sheets = $this->sheetRepository->getSheetsInCatalogByEvent($query->event);

        $sheetViews = [];

        /** @var Sheet $sheet */
        foreach ($sheets as $sheet) {
            $indicator = $this->indicatorCalculator->getIndicator($sheet);

            $sheetViews[] = new SheetView(
                $sheet->getId(),
                $this->types[$sheet->getType()->getId()],
                $indicator->sheetsPlanningQuantity,
                $indicator->possibleMeetingsQuantity
            );
        }

        return $sheetViews;
    }

    /**
     * @param SheetViewQuery $query
     */
    private function orderTypeById(SheetViewQuery $query)
    {
        foreach ($query->types as $type) {
            $this->types[$type->id] = $type;
        }
    }
}
