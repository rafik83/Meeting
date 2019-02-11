<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Planner;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class PlanningQuantityGuesser
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Merger
     */
    private $orderMerger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Merger                   $orderMerger
     */
    public function __construct(OrderRepositoryInterface $orderRepository, Merger $orderMerger)
    {
        $this->orderRepository = $orderRepository;
        $this->orderMerger     = $orderMerger;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function guess(Sheet $sheet): int
    {
        // If the sheet has no package,
        // Or if the option a participant = a planning
        // the number of planning is equal to the number of participant
        if (!$sheet->getPackage()->isPassable() || $sheet->getPackage()->isParticipantWithPlanning()) {
            return $sheet->countParticipants();
        }

        // Otherwise, the number of planning is equal to the number of planning bought by the sheet
        $orders = $this->orderRepository->findNotCancelledBySheet($sheet);

        if (0 < \count($orders)) {
            $orderMerged = $this->orderMerger->merge($orders);

            return $orderMerged->countPlanning();
        }

        return 0;
    }
}
