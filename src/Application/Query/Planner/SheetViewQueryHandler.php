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
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
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
     * @var Merger
     */
    private $orderMerger;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param Merger                   $orderMerger
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        OrderRepositoryInterface $orderRepository,
        Merger $orderMerger
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->orderRepository = $orderRepository;
        $this->orderMerger     = $orderMerger;
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
            $planning = 0;

            if (!$sheet->getPackage()->isPassable()) {
                $planning = $sheet->countParticipant();
            } else {
                $orders   = $this->orderRepository->findBySheet($sheet);
                if (0 < count($orders)) {
                    $orderMerge = $this->orderMerger->merge($orders);
                    $planning = $orderMerge->countPlanning();
                }
            }

            // TO DO, get the possible meeting quantity
            $sheetViews[] = new SheetView(
                $sheet->getId(),
                $this->types[$sheet->getType()->getId()],
                $planning,
                1
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
