<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
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
     * @var float
     */
    public $total;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @param int                $id
     * @param string             $numero
     * @param int                $sheetId
     * @param string             $sheetTitle
     * @param string             $sheetType
     * @param string             $follower
     * @param \DateTimeInterface $createdAt
     * @param float              $total
     * @param string             $vatMode
     * @param string             $currency
     */
    public function __construct(
        $id,
        $numero,
        $sheetId,
        $sheetTitle,
        $sheetType,
        $follower,
        \DateTimeInterface $createdAt,
        $total,
        $vatMode,
        $currency
    ) {
        $this->id         = $id;
        $this->numero     = $numero;
        $this->sheetId    = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->sheetType  = $sheetType;
        $this->follower   = $follower;
        $this->createdAt  = $createdAt;
        $this->total      = $total;
        $this->vatMode    = $vatMode;
        $this->currency   = $currency;
    }
}
