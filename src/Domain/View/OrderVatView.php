<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class OrderVatView
{
    /** @var string */
    public $numero;

    /** @var int */
    public $orderId;

    /** @var int */
    public $sheetId;

    /** @var bool */
    public $isVatApplicable;

    /** @var float */
    public $vatRate;

    /** @var string */
    public $vatMode;

    /** @var string */
    public $currency;

    /** @var bool */
    public $isCancelled;

    /** @var int in cents */
    public $totalWithoutVat;

    /** @var int in cents */
    public $vatAmount;

    /** @var int in cents */
    public $totalWithVat;

    /** @var \DateTimeInterface */
    public $createdAt;

    /**
     * @param string             $numero
     * @param int                $orderId
     * @param int                $sheetId
     * @param bool               $isVatApplicable
     * @param float              $vatRate
     * @param string             $vatMode
     * @param string             $currency
     * @param bool               $isCancelled
     * @param int                $totalWithoutVat
     * @param int                $vatAmount
     * @param int                $totalWithVat
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        $numero,
        $orderId,
        $sheetId,
        $isVatApplicable,
        $vatRate,
        $vatMode,
        $currency,
        $isCancelled,
        $totalWithoutVat,
        $vatAmount,
        $totalWithVat,
        \DateTimeInterface $createdAt
    ) {
        $this->numero          = $numero;
        $this->orderId         = $orderId;
        $this->sheetId         = $sheetId;
        $this->isVatApplicable = $isVatApplicable;
        $this->vatRate         = $vatRate;
        $this->vatMode         = $vatMode;
        $this->currency        = $currency;
        $this->isCancelled     = $isCancelled;
        $this->totalWithoutVat = $totalWithoutVat;
        $this->vatAmount       = $vatAmount;
        $this->totalWithVat    = $totalWithVat;
        $this->createdAt       = $createdAt;
    }
}
