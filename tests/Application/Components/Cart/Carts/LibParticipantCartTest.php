<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Cart\Carts;

use Proximum\Vimeet\Application\Components\Cart\CartRow;
use Proximum\Vimeet\Application\Components\Cart\Carts\LibParticipantCart;
use Proximum\Vimeet\Application\Components\Product\Products\LibParticipantProduct;

class LibParticipantCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $product   = new LibParticipantProduct('key');
        $product->setOptions([]);
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibParticipantCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepareWithEmptyTemplate()
    {
        $product   = new LibParticipantProduct('key');
        $product->setOptions([]);
        $dataValue = [
            "participant"        => true,
            "quantity" => 2,
        ];
        $locale    = 'fr';

        $optionCart = new LibParticipantCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepare()
    {
        $product  = new LibParticipantProduct('key');
        $product->setOptions([
            "label"       => [
                "fr" => "Ajouter des participants",
                "en" => "Add participants",
            ],
            "description" => [
                "fr" => "Vous pouvez ajouter des participants à votre fiche",
                "en" => "En Anglais=>  Vous pouvez ajouter des participants à votre fiche",
            ],
            "required"    => false,
            "type"        => "lib_participant",
            "unitPrice"   => 400,
        ]);

        $dataValue = [
            'participant'        => true,
            'quantity' => 3,
        ];

        $result = new CartRow('Ajouter des participants', 3, 400);

        $locale = 'fr';

        $optionCart = new LibParticipantCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals($result, $cart);
    }
}
