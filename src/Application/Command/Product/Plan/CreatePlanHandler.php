<?php

namespace Proximum\Vimeet\Application\Command\Product\Plan;

use Proximum\Vimeet\Application\Command\Product\AbstractHandler;
use Proximum\Vimeet\Domain\Model\Product;

class CreatePlanHandler extends AbstractHandler
{
    /**
     * @param CreatePlan $createPlan
     */
    public function handle(CreatePlan $createPlan)
    {
        $product = Product::createPlan(
            $createPlan->event,
            $createPlan->name,
            $this->fileStorageInterface->upload($createPlan->file),
            $createPlan->unitPrice,
            $createPlan->vat,
            $createPlan->availabilityCurrent,
            $createPlan->availabilityMax
        );

        foreach ($createPlan->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], $translation['heading'], $translation['description'], $translation['addon'], null);
        }

        foreach ($createPlan->productIncluded as $productIncluded) {
            $product->includeProduct($productIncluded['product'], $productIncluded['quantity']);
        }

        foreach ($createPlan->features as $feature) {
            $object = new Product\Feature($product);

            foreach ($feature['translations'] as $locale => $translation) {
                $object->translate($locale, $translation['title'], $translation['description']);
            }

            $product->addFeature($object);
        }

        $this->productRepository->add($product);
    }
}
