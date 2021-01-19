<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class PromotionCodeView
{
    /** @var int */
    public $promotionCodeId;

    /** @var string */
    public $promotionCodeTitle;

    /** @var string */
    public $promotionCodeTitleWithQuantityTranslation;

    /** @var string */
    public $promotionCodeTitleWithTotalTranslation;

    /**
     * @param int    $promotionCodeId
     * @param string $promotionCodeTitle
     * @param string $promotionCodeTitleWithQuantityTranslation
     * @param string $promotionCodeTitleWithTotalTranslation
     */
    public function __construct(
        $promotionCodeId,
        $promotionCodeTitle,
        $promotionCodeTitleWithQuantityTranslation,
        $promotionCodeTitleWithTotalTranslation
    ) {
        $this->promotionCodeId                           = $promotionCodeId;
        $this->promotionCodeTitle                        = $promotionCodeTitle;
        $this->promotionCodeTitleWithQuantityTranslation = $promotionCodeTitleWithQuantityTranslation;
        $this->promotionCodeTitleWithTotalTranslation    = $promotionCodeTitleWithTotalTranslation;
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
