<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\View\Sheet\DashboardView;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class DashboardViewQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * DashboardViewQueryHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param Balance                  $balance
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        Balance $balance
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->balance         = $balance;
    }

    /**
     * @param DashboardViewQuery $dashboardViewQuery
     *
     * @return DashboardView
     */
    public function handle(DashboardViewQuery $dashboardViewQuery)
    {
        $sheets = $this->sheetRepository->getEnabledSheetsByEvent($dashboardViewQuery->event);

        $dashboardView = new DashboardView();

        foreach ($sheets as $sheet) {
            if ($sheet->hasOrders()) {
                $dashboardView->totalPaid           += $this->balance->getTotalPaid($sheet);
                $dashboardView->totalOrders         += $this->balance->getTotal($sheet);
                $dashboardView->totalRemainingToPay += $this->balance->getRemainingToPay($sheet);
            }
        }

        return $dashboardView;
    }
}
