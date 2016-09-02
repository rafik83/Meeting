<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
        $product = $updateParticipant->product->updateParticipant(
            $updateParticipant->name,
            $updateParticipant->quantityMax,
            $this->updatePriceResolver->resolve($updateParticipant->product) ? $updateParticipant->unitPrice : null
        );

        foreach ($updateParticipant->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], null, null);
        }

        $this->productRepository->update($product);
    }
}
