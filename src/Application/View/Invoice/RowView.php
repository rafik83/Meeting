<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class RowView
{
    /** @var string */
    public $label;

    /** @var int */
    public $quantity;

    /** @var int in cents */
    public $price;

    /** @var int in cents */
    public $total;

    /** @var int */
    public $productId;

    /** @var CustomRowView[] */
    public $customRowViews = [];

    /** @var IncludedProductView[] */
    public $includedProductViews = [];

    /**
     * @param string                $label
     * @param int                   $quantity
     * @param int                   $price
     * @param int                   $total
     * @param int                   $productId
     * @param CustomRowView[]       $customRowViews
     * @param IncludedProductView[] $includedProductViews
     */
    public function __construct(
        $label,
        $quantity,
        $price,
        $total,
        $productId,
        array $customRowViews = [],
        array $includedProductViews = []
    ) {
        $this->label                = $label;
        $this->quantity             = $quantity;
        $this->price                = $price;
        $this->total                = $total;
        $this->productId            = $productId;
        $this->customRowViews       = $customRowViews;
        $this->includedProductViews = $includedProductViews;
    }
}
