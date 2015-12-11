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
use Proximum\Vimeet\Application\Components\Cart\Carts\LibChoiceWithDescriptionCart;
use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceWithDescriptionProduct;

class LibChoiceWithDescriptionCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $product   = new LibChoiceWithDescriptionProduct('key');
        $product->setOptions([]);
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibChoiceWithDescriptionCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepareWithEmptyTemplate()
    {
        $product   = new LibChoiceWithDescriptionProduct('563cb15fbf353');
        $product->setOptions([]);
        $dataValue = [
            "value" => '563cb15fbf353',
        ];

        $locale = 'fr';

        $optionCart = new LibChoiceWithDescriptionCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepare()
    {
        $expectedProduct  = new LibChoiceWithDescriptionProduct('563cae8a5072d');
        $expectedProductOption = [
            'label'       =>
                ['fr' => 'Forfait'],
            'description' =>
                ['fr' => 'Description forfait fr'],
            'type'        => 'choice_with_description',
            'required'    => true,
            'choices'     => [
                '563cb13a4d3de' => [
                    'label'       =>
                        [
                            'fr' => 'Forfait Silver',
                            'en' => 'Silver formula',
                        ],
                    'description' =>
                        [
                            'fr' => 'Description forfait  Silver',
                            'en' => 'Description Silver',
                        ],
                    'unitPrice'   => 3990,
                ],
                '563cb1546dd70' => [
                    'label'       =>
                        [
                            'fr' => 'Forfait Gold',
                            'en' => 'Gold formula',
                        ],
                    'description' =>
                        [
                            'fr' => 'Description forfait  Gold',
                            'en' => 'Description Gold',
                        ],
                    'unitPrice'   => 5990,
                ],
            ],
        ];
        $expectedProduct->setOptions($expectedProductOption);
        $expectedChoiceProduct  = new LibChoiceProduct('563cb13a4d3de');
        $expectedChoiceProductOption = [
            'label'       =>
                [
                    'fr' => 'Forfait Silver',
                    'en' => 'Silver formula',
                ],
            'description' =>
                [
                    'fr' => 'Description forfait  Silver',
                    'en' => 'Description Silver',
                ],
            'unitPrice'   => 3990,
        ];
        $expectedChoiceProduct->setOptions($expectedChoiceProductOption);
        $expectedChoiceProduct2 = new LibChoiceProduct('563cb1546dd70');
        $expectedChoiceProduct2Option = [
            'label'       =>
                [
                    'fr' => 'Forfait Gold',
                    'en' => 'Gold formula',
                ],
            'description' =>
                [
                    'fr' => 'Description forfait  Gold',
                    'en' => 'Description Gold',
                ],
            'unitPrice'   => 5990,
        ];
        $expectedChoiceProduct2->setOptions($expectedChoiceProduct2Option);
        $expectedChoiceProduct2->setChoiceParent($expectedProduct);
        $expectedChoiceProduct->setChoiceParent($expectedProduct);
        $expectedProduct->addChoice($expectedChoiceProduct);
        $expectedProduct->addChoice($expectedChoiceProduct2);

        $dataValue = [
            'value'    => '563cb13a4d3de',
        ];

        $result = new CartRow('Forfait : Forfait Silver', 1, 3990);

        $locale = 'fr';

        $optionCart = new LibChoiceWithDescriptionCart();
        $cart       = $optionCart->prepare($expectedProduct, $dataValue, $locale);

        $this->assertEquals($result, $cart);
    }
}
