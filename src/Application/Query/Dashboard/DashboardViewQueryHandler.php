<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardView;
use Proximum\Vimeet\Domain\Order\Balance;

class DashboardViewQueryHandler
{
    /**
     * @var Balance
     */
    private $balance;

    /**
     * DashboardViewQueryHandler constructor.
     *
     * @param Balance $balance
     */
    public function __construct(Balance $balance)
    {
        $this->balance = $balance;
    }

    /**
     * @param DashboardViewQuery $dashboardViewQuery
     *
     * @return DashboardView
     */
    public function handle(DashboardViewQuery $dashboardViewQuery)
    {
        $this->balance->loadAllTransactions($dashboardViewQuery->event);
        $this->balance->loadAllOrdersByEvent($dashboardViewQuery->event);

        $totalOrders         = $this->balance->getOrdersTotal($dashboardViewQuery->event);
        $totalPaid           = $this->balance->getTransactionsTotalPaid($dashboardViewQuery->event);
        $totalRemainingToPay = $this->balance->getOrdersTotalRemainingToPay($dashboardViewQuery->event);

        return new DashboardView($totalOrders, $totalRemainingToPay, $totalPaid);
    }
}
