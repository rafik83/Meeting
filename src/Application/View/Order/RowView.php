<?php

namespace Proximum\Vimeet\Application\View\Order;

class RowView
{
    /** @var int */
    public $id;

    /** @var string */
    public $label;

    /** @var int */
    public $quantity;

    /** @var float */
    public $price;

    /** @var float */
    public $total;

    /** @var string */
    public $vatMode;

    /** @var float */
    public $vatRate;

    /** @var string */
    public $currency;

    /** @var IncludedProductView[] */
    public $includedProducts = [];

    /** @var null|\DateTimeInterface */
    public $buyableUntil;

    /** @var null|\DateTimeInterface */
    public $deletableUntil;

    /** @var bool */
    public $isBuyable;

    /** @var bool */
    public $isDeletable;

    /** @var null|CustomRowView[] */
    public $customRows = [];

    /** @var int */
    public $productId;

    public function __construct(
        int $id,
        int $productId,
        string $label,
        float $price,
        int $quantity,
        string $vatMode,
        float $vatRate,
        string $currency,
        \DateTimeInterface $buyableUntil = null,
        \DateTimeInterface $deletableUntil = null,
        bool $isBuyable,
        bool $isDeletable
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->total = $price * $quantity;
        $this->vatMode = $vatMode;
        $this->vatRate = $vatRate;
        $this->currency = $currency;
        $this->buyableUntil = $buyableUntil;
        $this->deletableUntil = $deletableUntil;
        $this->isBuyable = $isBuyable;
        $this->isDeletable = $isDeletable;
        $this->productId = $productId;
    }

    public function addIncludedProduct(IncludedProductView $productView)
    {
        $this->includedProducts[] = $productView;
    }

    public function addCustomRow(CustomRowView $customRowView)
    {
        $this->customRows[] = $customRowView;
    }
}
