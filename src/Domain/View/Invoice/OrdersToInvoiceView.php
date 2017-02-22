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

    /** @var float */
    private $total;

    /** @var float */
    private $vatAmount;

    /** @var float */
    private $totalWithVat;

    /** @var array */
    private $data;

    /**
     * @param array $orders
     * @param array $data
     * @param float $total
     * @param float $vatAmount
     * @param float $totalWithVat
     */
    public function __construct(array $orders, array $data, $total, $vatAmount, $totalWithVat)
    {
        $this->orders = $orders;
        $this->total = $total;
        $this->vatAmount = $vatAmount;
        $this->totalWithVat = $totalWithVat;
        $this->data = $data;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return float
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @return float
     */
    public function getTotalWithVat()
    {
        return $this->totalWithVat;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
