<?php

namespace Proximum\Vimeet\Application\View\Dashboard;

class DashboardTransactionView
{
    /**
     * @var float
     */
    public $totalOrders = 0;

    /**
     * @var float
     */
    public $totalRemainingToPay = 0;

    /**
     * @var float
     */
    public $totalPaid = 0;

    /**
     * DashboardView constructor.
     *
     * @param float $totalOrders
     * @param float $totalRemainingToPay
     * @param float $totalPaid
     */
    public function __construct($totalOrders, $totalRemainingToPay, $totalPaid)
    {
        $this->totalOrders         = $totalOrders;
        $this->totalRemainingToPay = $totalRemainingToPay;
        $this->totalPaid           = $totalPaid;
    }
}
