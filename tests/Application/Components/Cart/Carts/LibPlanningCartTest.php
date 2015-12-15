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
use Proximum\Vimeet\Application\Components\Cart\Carts\LibPlanningCart;
use Proximum\Vimeet\Application\Components\Product\Products\LibPlanningProduct;

class LibPlanningCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $product   = new LibPlanningProduct('key');
        $product->setOptions([]);
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibPlanningCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepareWithEmptyTemplate()
    {
        $product   = new LibPlanningProduct('key');
        $product->setOptions([]);
        $dataValue = [
            "planning"        => true,
            "planning_bought" => 2,
        ];
        $locale    = 'fr';

        $optionCart = new LibPlanningCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepare()
    {
        $product   = new LibPlanningProduct('key');
        $product->setOptions([
            'label'       => [
                'fr' => 'Ajouter des plannings',
                'en' => 'Add plannings',
            ],
            'description' => [
                'fr' => 'Vous pouvez ajouter des plannings à votre fiche',
                'en' => 'En Anglais: Vous pouvez ajouter des plannings à votre fiche',
            ],
            'required'    => false,
            'type'        => 'lib_planning',
            'unitPrice'   => 550,
        ]);

        $dataValue = [
            'planning'        => true,
            'planning_bought' => 2,
        ];

        $result = new CartRow('Ajouter des plannings', 2, 550);

        $locale = 'fr';

        $optionCart = new LibPlanningCart();
        $cart       = $optionCart->prepare($product, $dataValue, $locale);

        $this->assertEquals($result, $cart);
    }
}
