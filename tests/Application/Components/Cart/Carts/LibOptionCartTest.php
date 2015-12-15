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
use Proximum\Vimeet\Application\Components\Cart\Carts\LibOptionCart;
use Proximum\Vimeet\Application\Components\Product\Including;
use Proximum\Vimeet\Application\Components\Product\Products\LibOptionProduct;

class LibOptionCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $product   = new LibOptionProduct('key');
        $product->setOptions([]);
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibOptionCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepareWithEmptyTemplate()
    {
        $product   = new LibOptionProduct('key');
        $product->setOptions([]);
        $dataValue = [
            "value"    => true,
            "quantity" => 2,
        ];

        $locale    = 'fr';

        $optionCart = new LibOptionCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepare()
    {
        $product = new LibOptionProduct('key');
        $product->setOptions([
            'label' => [
                'fr' => "Affichage logo sur site événement et supports de communication",
            ],
            'description' => [
                'fr' => "test",
            ],
            'type' => "lib_option",
            'unitPrice' => 200,
            'quantity' => [
                'min'   => 1,
                'max'   => 5,
                'range' => 1,
            ],
        ]);

        $dataValue = [
            'value'    => true,
            'quantity' => 3,
        ];

        $result = new CartRow(
            'Affichage logo sur site événement et supports de communication',
            3,
            200
        );

        $locale = 'fr';

        $optionCart = new LibOptionCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals($result, $cart);
    }

    public function testPrepareWithInclude()
    {
        $product = new LibOptionProduct('key');
        $product->setOptions([
            'label' => [
                'fr' => "Affichage logo sur site événement et supports de communication",
            ],
            'description' => [
                'fr' => "test",
            ],
            'type' => "lib_option",
            'unitPrice' => 200,
            'quantity' => [
                'min'   => 1,
                'max'   => 5,
                'range' => 1,
            ],
        ]);

        $product2 = new LibOptionProduct('yek');
        $product2->setOptions([
            'label' => [
                'fr' => "Test include",
            ],
            'description' => [
                'fr' => "test",
            ],
            'type' => "lib_option",
        ]);

        $including = new Including($product, $product2, 1);

        $dataValue = [
            'value'    => true,
            'quantity' => 3,
        ];

        $result = new CartRow(
            'Affichage logo sur site événement et supports de communication',
            3,
            200
        );
        $include = new CartRow(
            'Test include',
            1,
            0
        );
        $result->addInclude($include);

        $locale = 'fr';

        $optionCart = new LibOptionCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals($result, $cart);
    }
}
