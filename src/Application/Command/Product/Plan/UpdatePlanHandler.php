<?php

namespace Proximum\Vimeet\Application\Command\Product\Plan;

use Proximum\Vimeet\Application\Command\Product\AbstractHandler;

class UpdatePlanHandler extends AbstractHandler
{
    /**
     * @param UpdatePlan $updatePlan
     */
    public function handle(UpdatePlan $updatePlan)
    {
        $canUpdatePriceAndVat = $this->updatePriceResolver->resolve($updatePlan->product);
        $updatePlan->product->updatePlan(
            $updatePlan->name,
            $this->fileStorageInterface->upload($updatePlan->file),
            $updatePlan->availabilityCurrent,
            $updatePlan->availabilityMax,
            $canUpdatePriceAndVat ? $updatePlan->unitPrice : $updatePlan->product->getUnitPrice(),
            $canUpdatePriceAndVat ? $updatePlan->vat : $updatePlan->product->getVat()
        );

        foreach ($updatePlan->translations as $locale => $translation) {
            $updatePlan->product->translate(
                $locale,
                $translation['title'],
                $translation['heading'],
                $translation['description'],
                $translation['addon'],
                null
            );
        }

        // Add products
        foreach ($updatePlan->productIncluded as $productIncluded) {
            $updatePlan->product->includeProduct($productIncluded['product'], $productIncluded['quantity']);
        }

        // Remove products
        foreach ($updatePlan->product->getIncludedProducts() as $includedProduct) {
            if (!$updatePlan->hasProduct($includedProduct->getIncluded())) {
                $updatePlan->product->removeIncludeProduct($includedProduct);
            }
        }

        // Add features
        foreach ($updatePlan->features as $key => $feature) {
            foreach ($feature['translations'] as $locale => $translation) {
                $updatePlan
                    ->product
                    ->getFeature($key)
                    ->translate($locale, $translation['title'], $translation['description']);
            }
        }

        // Remove features
        foreach ($updatePlan->product->getFeatures() as $key => $feature) {
            if (!isset($updatePlan->features[$key])) {
                $updatePlan->product->removeFeature($feature);
            }
        }

        // update product on database
        $this->productRepository->update($updatePlan->product);
    }
}
