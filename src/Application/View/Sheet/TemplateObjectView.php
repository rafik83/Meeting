<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class TemplateObjectView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var array
     */
    public $includedProductIds;

    /**
     * @var TemplateObject
     */
    public $templateObject;

    /**
     * @var bool
     */
    public $hasBuyableIncludedProduct;

    /**
     * TemplateObjectView constructor.
     *
     * @param TemplateObject $templateObject
     * @param string         $label
     * @param bool           $hasBuyableIncludedProduct
     */
    public function __construct(
        TemplateObject $templateObject,
        $label,
        $hasBuyableIncludedProduct
    ) {
        $this->label                     = $label;
        $this->hasBuyableIncludedProduct = $hasBuyableIncludedProduct;
        $this->templateObject            = $templateObject;
    }

    /**
     * @return bool
     */
    public function hasBuyableIncludedProduct()
    {
        return $this->hasBuyableIncludedProduct;
    }
}
