<?php

namespace Proximum\Vimeet\Application\View\Order;

class IncludedProductView
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

    /**
     * @param int                     $id
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
    }
}
