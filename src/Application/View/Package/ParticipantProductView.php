<?php

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantProductView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var float */
    public $unitPrice;

    /** @var string */
    public $currency;

    /** @var string */
    public $vatMode;

    /** @var float */
    public $quantityMax;

    /** @var int */
    public $quantityIncluded;

    /** @var bool */
    public $isBuyable;

    /** @var bool */
    public $hasRemainingIncludedQuantity;

    /** @var int */
    public $remainingQuantityIncluded;

    /**
     * @param int    $id
     * @param string $title
     * @param string $description
     * @param float  $unitPrice
     * @param string $currency
     * @param string $vatMode
     * @param float  $quantityMax
     * @param int    $quantityIncluded
     * @param bool   $isBuyable
     * @param int    $remainingQuantityIncluded
     * @param bool   $hasRemainingIncludedQuantity
     */
    public function __construct(
        int $id,
        string $title,
        string $description,
        float $unitPrice,
        string $currency,
        string $vatMode,
        float $quantityMax,
        int $quantityIncluded,
        bool $isBuyable,
        int $remainingQuantityIncluded,
        bool $hasRemainingIncludedQuantity
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->unitPrice = $unitPrice;
        $this->currency = $currency;
        $this->vatMode = $vatMode;
        $this->quantityMax = $quantityMax;
        $this->quantityIncluded = $quantityIncluded;
        $this->isBuyable = $isBuyable;
        $this->remainingQuantityIncluded = $remainingQuantityIncluded;
        $this->hasRemainingIncludedQuantity = $hasRemainingIncludedQuantity;
    }

    /**
     * @return bool
     */
    public function isInfiniteQuantityMax(): bool
    {
        return INF === $this->quantityMax;
    }
}
