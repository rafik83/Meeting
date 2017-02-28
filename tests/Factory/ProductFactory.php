<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;

class ProductFactory
{
    /**
     * @param Event $event
     *
     * @return Product
     */
    public static function create(Event $event)
    {
        return new Product(
            $event,
            'type',
            'productName',
            'image',
            25.0,
            5,
            2,
            5,
            true
        );
    }
}
