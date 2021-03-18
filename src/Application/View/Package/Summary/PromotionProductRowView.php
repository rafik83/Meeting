<?php

namespace Proximum\Vimeet\Application\View\Package\Summary;

use Proximum\Vimeet\Domain\Model\Promotion;

class PromotionProductRowView
{
    /** @var Promotion */
    public $promotion;

    /** @var string */
    public $product;

    /** @var string */
    public $promotionType;

    /**
     * Discount applicable for the product (can be a value to substract, a percentage, an offer)
     *
     * @var float
     */
    public $discountValue;

    /** @var int */
    public $quantity;

    /** @var float */
    public $vatRate;

    /**
     * Value of the discount for this product
     *
     * @var float
     */
    public $totalDiscount;

    /**
     * @param Promotion $promotion
     * @param string    $product
     * @param string    $promotionType
     * @param int|float $discountValue
     * @param int       $quantity
     * @param float     $vatRate
     * @param float     $totalDiscount
     */
    public function __construct(
        Promotion $promotion,
        $product,
        $promotionType,
        $discountValue,
        $quantity,
        float $vatRate,
        float $totalDiscount
    ) {
        $this->promotion     = $promotion;
        $this->product       = $product;
        $this->promotionType = $promotionType;
        $this->quantity      = $quantity;
        $this->discountValue = -1 * $discountValue;
        $this->vatRate       = $vatRate;
        $this->totalDiscount = $totalDiscount;
    }
}
