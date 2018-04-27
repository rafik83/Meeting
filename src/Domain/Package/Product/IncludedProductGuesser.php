<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Model\Sheet;

class IncludedProductGuesser extends AbstractIncludedProductGuesser
{
    /**
     * @param Sheet $sheet
     *
     * @return int[]
     */
    public function getIncludedProductIds(Sheet $sheet)
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
