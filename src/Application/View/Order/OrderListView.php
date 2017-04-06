<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class OrderListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $numero;

    /**
     * @var int
     */
    public $sheetId;

    /**
     * @var string
     */
    public $sheetTitle;

    /**
     * @var string
     */
    public $sheetType;

    /**
     * @var string
     */
    public $follower;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * Amount without VAT
     *
     * @var float
     */
    public $totalWithoutVat;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var bool
     */
    public $isInvoiced;

    /**
     * @param int                $id
     * @param string             $numero
     * @param int                $sheetId
     * @param string             $sheetTitle
     * @param string             $sheetType
     * @param string             $follower
     * @param \DateTimeInterface $createdAt
     * @param float              $totalWithoutVat Amount without VAT
     * @param string             $vatMode
     * @param string             $currency
     * @param bool               $isInvoiced
     */
    public function __construct(
        $id,
        $numero,
        $sheetId,
        $sheetTitle,
        $sheetType,
        $follower,
        \DateTimeInterface $createdAt,
        $totalWithoutVat,
        $vatMode,
        $currency,
        $isInvoiced
    ) {
        $this->id              = $id;
        $this->numero          = $numero;
        $this->sheetId         = $sheetId;
        $this->sheetTitle      = $sheetTitle;
        $this->sheetType       = $sheetType;
        $this->follower        = $follower;
        $this->createdAt       = $createdAt;
        $this->totalWithoutVat = $totalWithoutVat;
        $this->vatMode         = $vatMode;
        $this->currency        = $currency;
        $this->isInvoiced      = $isInvoiced;
    }
}
