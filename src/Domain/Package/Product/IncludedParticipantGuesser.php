<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Model\Product\ProductIncluded;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;

class IncludedParticipantGuesser extends AbstractIncludedProductGuesser
{
    /**
     * @param Sheet $sheet
     *
     * @deprecated
     */
    public function getIncludedParticipantView(Sheet $sheet)
    {
    }

    /**
     * @param Sheet $sheet
     *
     * @return IncludedParticipantView[] indexed by included product id
     */
    public function getIncludedParticipantViews(Sheet $sheet): array
    {
        $includedParticipantViews = [];

        foreach ($this->getParticipantIncluded($sheet) as $productIncluded) {
            $includedParticipantViews[$productIncluded->getIncluded()->getId()] = new IncludedParticipantView(
                $productIncluded->getIncluded(),
                $productIncluded->getQuantity(),
                0 // @todo
            );
        }

        return $includedParticipantViews;
    }

    /**
     * @param Sheet $sheet
     *
     * @return ProductIncluded[]
     */
    private function getParticipantIncluded(Sheet $sheet): array
    {
        $selectedPlan = $this->getSelectedPlan($sheet);

        if (null === $selectedPlan) {
            return [];
        }

        return $selectedPlan->getIncludedParticipantProducts();
    }
}
