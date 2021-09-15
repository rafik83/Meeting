<?php

namespace Proximum\Vimeet\Application\Command\Product\Plan;

use Proximum\Vimeet\Application\Command\Product\AbstractUpdate;
use Proximum\Vimeet\Domain\Model\Product;

class UpdatePlan extends AbstractUpdate
{
    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);

        foreach ($product->getFeatures() as $key => $feature) {
            $this->features[$key] = [];
            foreach ($product->getEvent()->getLocales() as $locale) {
                $this->features[$key]['translations'][$locale] = [
                    'title'       => $feature->getTitle($locale),
                    'description' => $feature->getDescription($locale),
                ];
            }
        }

        foreach ($product->getIncludedProducts() as $key => $includedProduct) {
            $this->productIncluded[$key] = [
                'product'  => $includedProduct->getIncluded(),
                'quantity' => $includedProduct->getQuantity(),
            ];
        }
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasProduct(Product $product)
    {
        foreach ($this->productIncluded as $includeProduct) {
            if ($includeProduct['product'] === $product) {
                return true;
            }
        }

        return false;
    }
}
