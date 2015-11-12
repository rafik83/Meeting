<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Cart\Carts;

use Proximum\Vimeet\Application\Components\Cart\Carts\LibParticipantCart;
use Symfony\Component\Config\Definition\Exception\Exception;

class LibParticipantCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $template  = [];
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibParticipantCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($cart !== []) {
            throw new Exception('Cart should be empty');
        }
    }

    public function testPrepareWithEmptyTemplate()
    {
        $template  = [];
        $dataValue = [
            "participant"        => true,
            "participant_bought" => 2,
        ];
        $locale    = 'fr';

        $optionCart = new LibParticipantCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($cart !== []) {
            throw new Exception('Cart should be empty');
        }
    }

    public function testPrepare()
    {
        $template = [
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
        ];

        $dataValue = [
            'participant'        => true,
            'participant_bought' => 3,
        ];

        $result = [
            'label'     => 'Ajouter des participants',
            'quantity'  => 3,
            'unitPrice' => 400,
            'total'     => 1200,
        ];

        $locale = 'fr';

        $optionCart = new LibParticipantCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($result !== $cart) {
            throw new Exception('Cart should not be empty');
        }
    }
}
