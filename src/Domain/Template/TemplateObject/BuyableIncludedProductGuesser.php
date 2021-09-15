<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Package\Product\IncludedProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class BuyableIncludedProductGuesser
{
    /**
     * @var IncludedProductGuesser
     */
    private $includedProductGuesser;

    /**
     * BuyableIncludedProductGuesser constructor.
     *
     * @param IncludedProductGuesser $includedProductGuesser
     */
    public function __construct(IncludedProductGuesser $includedProductGuesser)
    {
        $this->includedProductGuesser = $includedProductGuesser;
    }

    /**
     * @param TemplateObject $templateObject
     *
     * @return bool
     */
    public function hasBuyableIncludedProduct(TemplateObject $templateObject)
    {
        $includedProductIds = $this->includedProductGuesser->getIncludedProductIds($templateObject->getSheet());

        foreach ($templateObject->getBuyableProducts() as $product) {
            if (in_array($product->getId(), $includedProductIds)) {
                return true;
            }
        }

        return false;
    }
}
