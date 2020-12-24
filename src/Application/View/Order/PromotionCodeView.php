<?php

namespace Proximum\Vimeet\Application\View\Order;

use Proximum\Vimeet\Application\View\Package\Summary\PromotionProductRowView;

class PromotionCodeView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $total;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var PromotionProductRowView[]
     */
    public $promotionProductRowViews;

    /** @var int */
    public $id;

    /**
     * @param int                       $id
     * @param string                    $label
     * @param string                    $description
     * @param float                     $total
     * @param string                    $vatMode
     * @param string                    $currency
     * @param PromotionProductRowView[] $promotionProductRowViews
     */
    public function __construct(int $id, $label, $description, $total, $vatMode, $currency, $promotionProductRowViews)
    {
        $this->id = $id;
        $this->label = $label;
        $this->description = $description;
        $this->total = $total;
        $this->quantity = 1;
        $this->vatMode = $vatMode;
        $this->currency = $currency;
        $this->promotionProductRowViews = $promotionProductRowViews;
    }
}
