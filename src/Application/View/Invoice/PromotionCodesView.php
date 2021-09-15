<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class PromotionCodesView
{
    /** @var PromotionCodeView[] */
    public $promotionCodeViews = [];

    /**
     * @param PromotionCodeView[] $promotionCodeViews
     */
    public function __construct(array $promotionCodeViews)
    {
        $this->promotionCodeViews = $promotionCodeViews;
    }
}
