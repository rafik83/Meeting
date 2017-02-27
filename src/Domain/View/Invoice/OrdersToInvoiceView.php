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

    /** @var int amount in centimes */
    private $total;

    /** @var int amount in centimes */
    private $vatAmount;

    /** @var int amount in centimes */
    private $totalWithVat;

    /** @var string */
    private $data;

    /**
     * @param array  $orders
     * @param string $data
     * @param int    $total
     * @param int    $vatAmount
     * @param int    $totalWithVat
     */
    public function __construct(array $orders, $data, $total, $vatAmount, $totalWithVat)
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
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @return int
     */
    public function getTotalWithVat()
    {
        return $this->totalWithVat;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
