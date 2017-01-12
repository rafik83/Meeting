<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Domain\Model\Product;

abstract class AbstractUpdate extends AbstractProduct
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var int
     */
    public $availabilityCurrent;

    /**
     * @var int
     */
    public $availabilityMax;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;

        $this->name                = $product->getName();
        $this->quantityMax         = $product->getQuantityMax();
        $this->unitPrice           = $product->getUnitPrice();
        $this->availabilityCurrent = $product->getAvailabilityCurrent();
        $this->availabilityMax     = $product->getAvailabilityMax();

        foreach ($product->getEvent()->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title'                     => $product->getTitle($locale),
                'heading'                   => $product->getHeading($locale),
                'description'               => $product->getDescription($locale),
                'addon'                     => $product->getAddon($locale),
                'subjectedToValidationHelp' => $product->getSubjectedToValidationHelp($locale),
            ];
        }
    }
}
