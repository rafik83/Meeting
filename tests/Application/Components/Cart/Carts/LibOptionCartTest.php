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

class LibOptionCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $template  = [];
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibOptionCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepareWithEmptyTemplate()
    {
        $template  = [];
        $dataValue = [
            "value"    => true,
            "quantity" => 2,
        ];

        $locale    = 'fr';

        $optionCart = new LibOptionCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepare()
    {
        $template = [
            'label'       => [
                'fr' => 'La mise en avant de votre entreprise au centre d’un e-mailing de promotion générale des Rendez-vous CARNOT dans la rubrique "Zoom sur"',
                'en' => 'The highlighting of your company in an e-mailing promoting Rendez-vous Carnot in the heading "zoom on"',
            ],
            'description' => [
                'fr' => 'Quantité min 3, quantité max 3',
                'en' => 'Quantité min 3, quantité max 3',
            ],
            'type'        => 'lib_option',
            'unitPrice'   => 5990,
            'quantity'    => [
                'min' => 3,
                'max' => 3,
            ],
        ];

        $dataValue = [
            'value'    => true,
            'quantity' => 3,
        ];

        $result = new CartRow(
            'La mise en avant de votre entreprise au centre d’un e-mailing de promotion générale des Rendez-vous CARNOT dans la rubrique "Zoom sur"',
            3,
            5990
        );

        $locale = 'fr';

        $optionCart = new LibOptionCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        $this->assertEquals($result, $cart);
    }
}
