<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class ProductBoughtView
{
    /** @var int */
    public $productId;

    /** @var float */
    public $unitPrice;

    /** @var int */
    public $quantity;

    /** @var float */
    public $total;

    /**
     * @param int   $productId
     * @param float $unitPrice
     * @param int   $quantity
     * @param float $total
     */
    public function __construct(
        $productId,
        $unitPrice,
        $quantity,
        $total
    ) {
        $this->productId = $productId;
        $this->unitPrice = $unitPrice;
        $this->quantity  = $quantity;
        $this->total     = $total;
    }

    /**
     * @return string
     */
    public function getUnitPriceColumnId()
    {
        return sprintf('product%sUnitPrice', $this->productId);
    }

    /**
     * @return string
     */
    public function getQuantityColumnId()
    {
        return sprintf('product%sQuantity', $this->productId);
    }

    /**
     * @return string
     */
    public function getTotalColumnId()
    {
        return sprintf('product%sTotal', $this->productId);
    }
}
