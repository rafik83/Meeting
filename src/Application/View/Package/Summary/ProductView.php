<?php

namespace Proximum\Vimeet\Application\View\Package\Summary;

class ProductView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var float */
    public $unitPrice;

    /** @var int */
    public $quantity;

    /** @var float */
    public $total = 0;

    /** @var string */
    public $vatMode;

    /** @var string */
    public $currency;

    /** @var IncludedView[] */
    public $included = [];

    /** @var float */
    public $vatRate;

    /**
     * @param int    $id
     * @param string $title
     * @param float  $unitPrice
     * @param int    $quantity
     * @param float  $total
     * @param string $vatMode
     * @param float  $vatRate
     * @param string $currency
     */
    public function __construct(
        $id,
        $title,
        $unitPrice,
        $quantity,
        $total,
        $vatMode,
        float $vatRate,
        $currency
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
        $this->total = $total;
        $this->vatMode = $vatMode;
        $this->currency = $currency;
        $this->vatRate = $vatRate;
    }

    /**
     * @param IncludedView $included
     *
     * @return ProductView
     */
    public function addIncludedProduct(IncludedView $included): ProductView
    {
        $this->included[] = $included;

        return $this;
    }
}
