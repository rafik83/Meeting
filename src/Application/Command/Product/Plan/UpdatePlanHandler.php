<?php


namespace Proximum\Vimeet\Application\Command\Product\Plan;


use Proximum\Vimeet\Application\Command\Product\AbstractHandler;
use Proximum\Vimeet\Domain\Model\Product\Feature;

class UpdatePlanHandler extends AbstractHandler
{
    /**
     * @param UpdatePlan $updatePlan
     */
    public function handle(UpdatePlan $updatePlan)
    {
        $product = $updatePlan->product->updatePlan(
            $updatePlan->name,
            $this->fileStorageInterface->upload($updatePlan->file),
            $updatePlan->unitPrice,
            $updatePlan->availabilityCurrent,
            $updatePlan->availabilityMax
        );

        foreach ($updatePlan->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], $translation['heading'], $translation['description'], $translation['addon'], null);
        }

        foreach ($updatePlan->productIncluded as $productIncluded) {
            $product->includeProduct($productIncluded['product'], $productIncluded['quantity']);
        }

        foreach ($updatePlan->features as $feature) {
            $object = new Feature($product);

            foreach ($feature['translations'] as $locale => $translation) {
                $object->translate($locale, $translation['title'], $translation['description']);
            }

            $product->addFeature($object);
        }

        $this->productRepository->update($product);
    }
}