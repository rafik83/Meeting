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

class LibChoiceWithDescriptionCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $template  = [];
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibChoiceWithDescriptionCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepareWithEmptyTemplate()
    {
        $template  = [];
        $dataValue = [
            "value" => '563cb15fbf353',
        ];

        $locale = 'fr';

        $optionCart = new LibChoiceWithDescriptionCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        $this->assertEquals(null, $cart);
    }

    public function testPrepare()
    {
        $template = [
            'label'       =>
                [
                    'fr' => 'Forfait',
                    'en' => 'Package',
                ],
            'description' =>
                [
                    'fr' => 'Description forfait fr',
                    'en' => 'Description package en',
                ],
            'type'        => 'choice_with_description',
            'required'    => true,
            'choices'     => [
                '563cb1257722e' => [
                    'label'         =>
                        [
                            'fr' => 'Formule Exposant',
                            'en' => 'Exhibitors package',
                        ],
                    'description'   =>
                        [
                            'fr' => 'Votre inscription aux Rendez-vous CARNOT comprend les prestations suivantes (valables pour 1 personne) :    - La publication de votre profil dans le catalogue envoyé aux participants par e-mail.    - L’accès au catalogue en ligne comprenant les « fiches de présentation » de chaque participant pour faire vos choix de rendez-vous.    - L’ouverture de votre « Espace Client » sur notre site internet.    - L’assistance logistique de notre équipe avant et pendant le salon.    - Un planning de rendez',
                            'en' => 'Description Exhibitors',
                        ],
                    'unitPrice'     => 2190,
                ],
                '563cb12b41909' => [
                    'label'         =>
                        [
                            'fr' => 'Formule Découverte',
                            'en' => 'Discovery formula',
                        ],
                    'description'   =>
                        [
                            'fr' => 'Description forfait Découverte',
                            'en' => 'Description Discovery',
                        ],
                    'unitPrice'     => 1090,
                ],
                '563cb13a4d3de' => [
                    'label'         =>
                        [
                            'fr' => 'Forfait Silver',
                            'en' => 'Silver formula',
                        ],
                    'description'   =>
                        [
                            'fr' => 'Description forfait  Silver',
                            'en' => 'Description Silver',
                        ],
                    'unitPrice'     => 3990,
                ],
                '563cb1546dd70' => [
                    'label'         =>
                        [
                            'fr' => 'Forfait Gold',
                            'en' => 'Gold formula',
                        ],
                    'description'   =>
                        [
                            'fr' => 'Description forfait  Gold',
                            'en' => 'Description Gold',
                        ],
                    'unitPrice'     => 5990,
                ],
                '563cb15fbf353' => [
                    'label'       =>
                        [
                            'fr' => 'Formule de sponsoring des CONFERENCES',
                            'en' => 'Sponsoring formula for CONFERENCES',
                        ],
                    'description' =>
                        [
                            'fr' => 'Description forfait  sponsoring',
                            'en' => 'Description sponsoring',
                        ],
                    'unitPrice'   => 11000,
                ],
            ],
        ];

        $dataValue = [
            'value'    => '563cb15fbf353',
        ];

        $result = new CartRow('Forfait : Formule de sponsoring des CONFERENCES', 1, 11000);

        $locale = 'fr';

        $optionCart = new LibChoiceWithDescriptionCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        $this->assertEquals($result, $cart);
    }
}
