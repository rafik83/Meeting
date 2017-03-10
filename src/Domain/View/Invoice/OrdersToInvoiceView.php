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

    /** @var string InvoiceDataView serialized in json */
    private $data;

    /** @var string string 3-letter ISO 4217 currency name */
    private $currency;

    /**
     * @param array  $orders
     * @param string $data InvoiceDataView serialized in json
     * @param int    $total amount in cents
     * @param int    $vatAmount amount in cents
     * @param int    $totalWithVat amount in cents
     * @param string $currency
     */
    public function __construct(array $orders, $data, $total, $vatAmount, $totalWithVat, $currency)
    {
        $this->orders       = $orders;
        $this->total        = $total;
        $this->vatAmount    = $vatAmount;
        $this->totalWithVat = $totalWithVat;
        $this->data         = $data;
        $this->currency     = $currency;
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
     * @return string InvoiceDataView serialized in json
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
