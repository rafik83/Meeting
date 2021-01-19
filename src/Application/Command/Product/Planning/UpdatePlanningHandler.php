<?php

namespace Proximum\Vimeet\Application\Command\Product\Planning;

use Proximum\Vimeet\Application\Command\Product\AbstractHandler;

class UpdatePlanningHandler extends AbstractHandler
{
    /**
     * @param UpdatePlanning $updatePlanning
     */
    public function handle(UpdatePlanning $updatePlanning)
    {
        $canUpdatePriceAndVat = $this->updatePriceResolver->resolve($updatePlanning->product);
        $product = $updatePlanning->product->updatePlanning(
            $updatePlanning->name,
            $updatePlanning->quantityMax,
            $canUpdatePriceAndVat ? $updatePlanning->unitPrice : $updatePlanning->product->getUnitPrice(),
            $canUpdatePriceAndVat ? $updatePlanning->vat : $updatePlanning->product->getVat()
        );

        foreach ($updatePlanning->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], null, null);
        }

        $this->productRepository->update($product);
    }
}
