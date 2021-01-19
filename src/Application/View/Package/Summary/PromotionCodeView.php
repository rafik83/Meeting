<?php

namespace Proximum\Vimeet\Application\View\Package\Summary;

class PromotionCodeView
{
    /**
     * PromotionCodeRow ID
     *
     * @var int
     */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var float */
    public $total;

    /** @var string */
    public $currency;

    /** @var string */
    public $vatMode;

    /** @var PromotionProductRowView[] */
    public $promotionProductRowViews;

    /**
     * @param int                       $id
     * @param string                    $title
     * @param string                    $description
     * @param float                     $total
     * @param string                    $currency
     * @param string                    $vatMode
     * @param PromotionProductRowView[] $promotionProductRowViews
     */
    public function __construct(
        $id,
        $title,
        $description,
        $total,
        $currency,
        $vatMode,
        array $promotionProductRowViews
    ) {
        $this->id                       = $id;
        $this->title                    = $title;
        $this->description              = $description;
        $this->total                    = $total;
        $this->currency                 = $currency;
        $this->vatMode                  = $vatMode;
        $this->promotionProductRowViews = $promotionProductRowViews;
    }
}
