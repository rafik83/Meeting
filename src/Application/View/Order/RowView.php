<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class RowView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var float
     */
    public $price;

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
     * @var IncludedProductView[]
     */
    public $includedProducts = [];

    /**
     * @var null|\DateTimeInterface
     */
    public $buyableUntil;

    /**
     * @var null|\DateTimeInterface
     */
    public $deletableUntil;

    /**
     * @var bool
     */
    public $isBuyable;

    /**
     * @var bool
     */
    public $isDeletable;

    /***
     * @var null|CustomRowView[]
     */
    public $customRows = [];

    /**
     * @var int
     */
    public $productId;

    /**
     * @param int                     $id
     * @param int                     $productId
     * @param string                  $label
     * @param float                   $price
     * @param int                     $quantity
     * @param string                  $vatMode
     * @param string                  $currency
     * @param null|\DateTimeInterface $buyableUntil
     * @param null|\DateTimeInterface $deletableUntil
     * @param bool                    $isBuyable
     * @param bool                    $isDeletable
     */
    public function __construct(
        $id,
        $productId,
        $label,
        $price,
        $quantity,
        $vatMode,
        $currency,
        \DateTimeInterface $buyableUntil = null,
        \DateTimeInterface $deletableUntil = null,
        $isBuyable,
        $isDeletable
    ) {
        $this->id             = $id;
        $this->label          = $label;
        $this->price          = $price;
        $this->quantity       = $quantity;
        $this->total          = $price * $quantity;
        $this->vatMode        = $vatMode;
        $this->currency       = $currency;
        $this->buyableUntil   = $buyableUntil;
        $this->deletableUntil = $deletableUntil;
        $this->isBuyable      = $isBuyable;
        $this->isDeletable    = $isDeletable;
        $this->productId      = $productId;
    }

    /**
     * @param IncludedProductView $productView
     */
    public function addIncludedProduct(IncludedProductView $productView)
    {
        $this->includedProducts[] = $productView;
    }

    /**
     * @param CustomRowView $customRowView
     */
    public function addCustomRow(CustomRowView $customRowView)
    {
        $this->customRows[] = $customRowView;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }
}
