<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardTransactionView;
use Proximum\Vimeet\Domain\Order\Balance;

class DashboardTransactionViewQueryHandler
{
    /** @var Balance */
    private $balance;

    /**
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
    public function handle(DashboardTransactionViewQuery $query): DashboardTransactionView
    {
        $this->balance->loadAllForEvent($query->event);

        $totalOrders         = $this->balance->getOrdersTotalWithoutVatForEvent();
        $totalPaid           = $this->balance->getTransactionsTotalPaidForEvent();
        $totalRemainingToPay = $this->balance->getTotalRemainingToPayForEvent();

        return new DashboardTransactionView($totalOrders, $totalRemainingToPay, $totalPaid);
    }
}
