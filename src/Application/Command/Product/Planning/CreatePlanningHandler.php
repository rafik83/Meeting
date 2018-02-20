<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Planning;

use Proximum\Vimeet\Application\Command\Product\AbstractHandler;
use Proximum\Vimeet\Domain\Model\Product;

class CreatePlanningHandler extends AbstractHandler
{
    /**
     * @param CreatePlanning $createPlanning
     */
    public function handle(CreatePlanning $createPlanning)
    {
        $product = Product::createPlanning(
            $createPlanning->event,
            $createPlanning->name,
            $createPlanning->unitPrice,
            $createPlanning->vat,
            $createPlanning->quantityMax
        );

        foreach ($createPlanning->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], null, null);
        }

        $this->productRepository->add($product);
    }
}
