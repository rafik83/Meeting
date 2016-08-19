<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class TemplateProductGuesser
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * TemplateProductGuesser constructor.
     *
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param TemplateObject $object
     * @param Package        $package
     *
     * @return null|array
     */
    public function getProducts(TemplateObject $object, Package $package)
    {
        $products = null;

        if (!$object->isBuyable()) {
            return null;
        }

        foreach ($object->getProducts() as $productId) {
            if ($option = $package->getOptionById((int)$productId)) {
                if ($option->isBuyable($this->dateTime) && !$option->isOutOfStock()) {
                    $products[] = $option;
                }
            }
        }

        return $products;
    }
}
