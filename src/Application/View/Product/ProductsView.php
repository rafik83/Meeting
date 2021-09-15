<?php

namespace Proximum\Vimeet\Application\View\Product;

class ProductsView
{
    /** @var string */
    public $name;

    /** @var float */
    public $unitPrice;

    /** @var int */
    public $quantityUnit;

    /** @var int */
    public $quantityPlan;

    /** @var int */
    public $quantityTotal;

    /** @var float */
    public $promotion;

    /** @var float|null */
    public $sales;

    public function __construct(
        string $name,
        float $unitPrice,
        int $quantityUnit,
        int $quantityPlan,
        int $quantityTotal,
        float $promotion,
        ?float $sales
    ) {
        $this->name = $name;
        $this->unitPrice = $unitPrice;
        $this->quantityUnit = $quantityUnit;
        $this->quantityPlan = $quantityPlan;
        $this->quantityTotal = $quantityTotal;
        $this->promotion = $promotion;
        $this->sales = $sales;
    }
}
