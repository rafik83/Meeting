<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;

class ProductFactory
{
    /**
     * @param Event  $event
     * @param string $type
     *
     * @return Product
     */
    public static function create(Event $event, $type = null)
    {
        return new Product(
            $event,
            null !== $type ? $type : 'option',
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
