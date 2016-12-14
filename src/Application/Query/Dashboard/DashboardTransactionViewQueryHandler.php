<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardTransactionView;
use Proximum\Vimeet\Domain\Order\Balance;

class DashboardTransactionViewQueryHandler
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
     * @param DashboardTransactionViewQuery $query
     *
     * @return DashboardTransactionView
     */
    public function handle(DashboardTransactionViewQuery $query)
    {
        $this->balance->loadAllTransactions($query->event);
        $this->balance->loadAllOrders($query->event);

        $totalOrders         = $this->balance->getOrdersTotal($query->event);
        $totalPaid           = $this->balance->getTransactionsTotalPaid($query->event);
        $totalRemainingToPay = $this->balance->getOrdersTotalRemainingToPay($query->event);

        return new DashboardTransactionView($totalOrders, $totalRemainingToPay, $totalPaid);
    }
}
