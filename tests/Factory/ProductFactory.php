<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;

class ProductFactory
{
    /**
     * @param Event  $event
     *
     * @param string $type
     *
     * @return Product
     */
    public static function create(Event $event, $type = null)
    {
        return new Product(
            $event,
            $type !== null ? $type : 'option',
            'productName',
            'image',
            25.0, // unitPrice
            20, // vat
            5, // quantityMax
            5, // availabilityCurrent
            2, // availabilityMax
            true
        );
    }
}
