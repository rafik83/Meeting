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
use Proximum\Vimeet\Domain\Model\Product;

class CreateParticipantHandler extends AbstractHandler
{
    /**
     * @param CreateParticipant $createParticipant
     */
    public function handle(CreateParticipant $createParticipant)
    {
        $product = Product::createParticipant(
            $createParticipant->event,
            $createParticipant->name,
            $createParticipant->unitPrice,
            $createParticipant->vat,
            $createParticipant->quantityMax
        );

        foreach ($createParticipant->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], null, null);
        }

        $this->productRepository->add($product);
    }
}
