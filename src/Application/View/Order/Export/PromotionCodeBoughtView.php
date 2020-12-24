<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class PromotionCodeBoughtView
{
    /** @var int */
    public $promotionCodeId;

    /** @var int */
    public $quantity;

    /** @var float */
    public $total;

    /**
     * @param int   $promotionCodeId
     * @param int   $quantity
     * @param float $total
     */
    public function __construct($promotionCodeId, $quantity, $total)
    {
        $this->promotionCodeId = $promotionCodeId;
        $this->quantity        = $quantity;
        $this->total           = $total;
    }

    /**
     * @return string
     */
    public function getQuantityColumnId()
    {
        return sprintf('promotionCode%sQuantity', $this->promotionCodeId);
    }

    /**
     * @return string
     */
    public function getTotalColumnId()
    {
        return sprintf('promotionCode%sTotal', $this->promotionCodeId);
    }
}
