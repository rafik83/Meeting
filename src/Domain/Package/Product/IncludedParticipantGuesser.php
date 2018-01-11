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
     * @return IncludedParticipantView
     */
    public function getIncludedParticipantView(Sheet $sheet)
    {
        $product             = null;
        $totalQuantity       = 0;
        $participantIncluded = $this->getParticipantIncluded($sheet);

        if (null !== $participantIncluded) {
            $totalQuantity = $participantIncluded->getQuantity();
            $product       = $participantIncluded->getIncluded();
        }

        $remainingQuantity = max(0, $totalQuantity - $sheet->countParticipant());

        return new IncludedParticipantView($product, $totalQuantity, $remainingQuantity);
    }

    /**
     * @param Sheet $sheet
     *
     * @return ProductIncluded
     *
     * @deprecated
     */
    private function getParticipantIncluded(Sheet $sheet)
    {
        $selectedPlan = $this->getSelectedPlan($sheet);

        if (null !== $selectedPlan) {
            $participantProductIncluded = $selectedPlan->getIncludedParticipantProduct();

            if ($participantProductIncluded instanceof ProductIncluded) {
                return $participantProductIncluded;
            }
        }

        return null;
    }
}
