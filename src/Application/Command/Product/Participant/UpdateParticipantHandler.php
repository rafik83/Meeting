<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Participant;

use Proximum\Vimeet\Application\Command\Product\AbstractHandler;

class UpdateParticipantHandler extends AbstractHandler
{
    /**
     * @param UpdateParticipant $updateParticipant
     */
    public function handle(UpdateParticipant $updateParticipant)
    {
        $canUpdatePriceAndVat = $this->updatePriceResolver->resolve($updateParticipant->product);
        $product = $updateParticipant->product->updateParticipant(
            $updateParticipant->name,
            $updateParticipant->quantityMax,
            $canUpdatePriceAndVat ? $updateParticipant->unitPrice : $updateParticipant->product->getUnitPrice(),
            $canUpdatePriceAndVat ? $updateParticipant->vat : $updateParticipant->product->getVat()
        );

        foreach ($updateParticipant->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], null, null);
        }

        $product->setAvailabilityTimeRanges($updateParticipant->availabilityTimeRanges);

        $this->productRepository->update($product);
    }
}
