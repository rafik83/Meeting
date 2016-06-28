<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Option;


use Proximum\Vimeet\Application\Command\Product\AbstractHandler;

class UpdateOptionHandler extends AbstractHandler
{
    /**
     * @param UpdateOption $updateOption
     */
    public function handle(UpdateOption $updateOption)
    {
        $product = $updateOption->product->updateOption(
            $updateOption->name,
            $this->fileStorageInterface->upload($updateOption->file),
            $updateOption->unitPrice,
            $updateOption->quantityMax,
            $updateOption->availabilityCurrent,
            $updateOption->availabilityMax,
            $updateOption->updatable,
            $updateOption->updatableUntil,
            $updateOption->subjectedToValidation,
            $updateOption->buyableUntil
        );

        foreach ($updateOption->translations as $locale => $translation) {
            $product->translate(
                $locale,
                $translation['title'],
                null,
                $translation['description'],
                $translation['addon'],
                $translation['subjectedToValidationHelp']
            );
        }

        $this->productRepository->update($product);
    }
}
