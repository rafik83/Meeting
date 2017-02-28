<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Invoice;

use Proximum\Vimeet\Domain\Model\Order;

class OrdersToInvoiceView
{
    /** @var Order[] */
    private $orders;

    /** @var int amount in cents */
    private $total;

    /** @var int amount in cents */
    private $vatAmount;

    /** @var int amount in cents */
    private $totalWithVat;

    /** @var string Order\SummaryView serialized in json */
    private $data;

    /**
     * @param array  $orders
     * @param string $data Order\SummaryView serialized in json
     * @param int    $total amount in cents
     * @param int    $vatAmount amount in cents
     * @param int    $totalWithVat amount in cents
     */
    public function __construct(array $orders, $data, $total, $vatAmount, $totalWithVat)
    {
        $this->orders       = $orders;
        $this->total        = $total;
        $this->vatAmount    = $vatAmount;
        $this->totalWithVat = $totalWithVat;
        $this->data         = $data;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return int amount in cents
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int amount in cents
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @return int amount in cents
     */
    public function getTotalWithVat()
    {
        return $this->totalWithVat;
    }

    /**
     * @return string Order\SummaryView serialized in json
     */
    public function getData()
    {
        return $this->data;
    }
}
