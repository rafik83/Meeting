<?php

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Model\Sheet;

class IncludedProductGuesser extends AbstractIncludedProductGuesser
{
    /**
     * @param Sheet $sheet
     *
     * @return int[]
     */
    public function getIncludedProductIds(Sheet $sheet): array
    {
        $selectedPlan = $this->getSelectedPlan($sheet);
        $includedProductIds = [];

        if (null !== $selectedPlan) {
            foreach ($selectedPlan->getIncludedProducts() as $includedProduct) {
                $includedProductIds[] = $includedProduct->getIncluded()->getId();
            }
        }

        return $includedProductIds;
    }
}
