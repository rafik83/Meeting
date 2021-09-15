<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class ProductView
{
    /** @var int */
    public $productId;

    /** @var string */
    public $productTitle;

    /** @var string */
    public $productTitleWithUnitPriceTranslation;

    /** @var string */
    public $productTitleWithQuantityTranslation;

    /** @var string */
    public $productTitleWithTotalTranslation;

    /**
     * @param int    $productId
     * @param string $productTitle
     * @param string $productTitleWithUnitPriceTranslation
     * @param string $productTitleWithQuantityTranslation
     * @param string $productTitleWithTotalTranslation
     */
    public function __construct(
        $productId,
        $productTitle,
        $productTitleWithUnitPriceTranslation,
        $productTitleWithQuantityTranslation,
        $productTitleWithTotalTranslation
    ) {
        $this->productId                            = $productId;
        $this->productTitle                         = $productTitle;
        $this->productTitleWithUnitPriceTranslation = $productTitleWithUnitPriceTranslation;
        $this->productTitleWithQuantityTranslation  = $productTitleWithQuantityTranslation;
        $this->productTitleWithTotalTranslation     = $productTitleWithTotalTranslation;
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
