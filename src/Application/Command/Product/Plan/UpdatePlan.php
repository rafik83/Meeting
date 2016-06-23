<?php


namespace Proximum\Vimeet\Application\Command\Product\Plan;


use Proximum\Vimeet\Application\Command\Product\AbstractUpdate;
use Proximum\Vimeet\Domain\Model\Product;

class UpdatePlan extends AbstractUpdate
{
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

        dump($this->productIncluded);
    }
}