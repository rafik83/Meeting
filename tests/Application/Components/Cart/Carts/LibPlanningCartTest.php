<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Cart\Carts;

use Proximum\Vimeet\Application\Components\Cart\Carts\LibPlanningCart;
use Symfony\Component\Config\Definition\Exception\Exception;

class LibPlanningCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $template  = [];
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibPlanningCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($cart !== []) {
            throw new Exception('Cart should be empty');
        }
    }

    public function testPrepareWithEmptyTemplate()
    {
        $template  = [];
        $dataValue = [
            "planning"        => true,
            "planning_bought" => 2,
        ];
        $locale    = 'fr';

        $optionCart = new LibPlanningCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($cart !== []) {
            throw new Exception('Cart should be empty');
        }
    }

    public function testPrepare()
    {
        $template = [
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
        ];

        $dataValue = [
            'planning'        => true,
            'planning_bought' => 2,
        ];

        $result = [
            'label'     => 'Ajouter des plannings',
            'quantity'  => 2,
            'unitPrice' => 550,
            'total'     => 1100,
        ];

        $locale = 'fr';

        $optionCart = new LibPlanningCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($result !== $cart) {
            throw new Exception('Cart should not be empty');
        }
    }
}
