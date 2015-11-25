<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Cart;

use Proximum\Vimeet\Application\Components\Cart\Cart;
use Proximum\Vimeet\Application\Components\Cart\CartBuilder;
use Proximum\Vimeet\Application\Components\Cart\CartRow;
use Proximum\Vimeet\Application\Components\Cart\Carts\LibChoiceWithDescriptionCart;
use Proximum\Vimeet\Application\Components\Cart\CartStep;

class CartBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCartBuilderWithEmptyTemplateAndData()
    {
        $template   = [];
        $dataValue  = [];
        $locale     = 'fr';
        $result     = new Cart(0);
        $cart       = new CartBuilder();
        $cartResult = $cart->create($template, $dataValue, $locale);

        $this->assertEquals($result, $cartResult);
    }

    public function testCartBuilder()
    {
        $template = [
            '563cae7496da1' => [
                'title'       => [
                    'fr' => 'Forfait de participation',
                    'en' => 'Participation package',
                ],
                'description' => [
                    'fr' => 'Description de l\'étape en français',
                    'en' => 'Step Description in english',
                ],
                'template'    => [
                    '563cae8a5072d' => [
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
                                'label'       =>
                                    [
                                        'fr' => 'Formule Exposant',
                                        'en' => 'Exhibitors package',
                                    ],
                                'description' =>
                                    [
                                        'fr' => 'Votre inscription aux Rendez-vous CARNOT comprend les prestations suivantes (valables pour 1 personne) :    - La publication de votre profil dans le catalogue envoyé aux participants par e-mail.    - L’accès au catalogue en ligne comprenant les « fiches de présentation » de chaque participant pour faire vos choix de rendez-vous.    - L’ouverture de votre « Espace Client » sur notre site internet.    - L’assistance logistique de notre équipe avant et pendant le salon.    - Un planning de rendez',
                                        'en' => 'Description Exhibitors',
                                    ],
                                'unitPrice'   => 2190,
                            ],
                            '563cb12b41909' => [
                                'label'       =>
                                    [
                                        'fr' => 'Formule Découverte',
                                        'en' => 'Discovery formula',
                                    ],
                                'description' =>
                                    [
                                        'fr' => 'Description forfait Découverte',
                                        'en' => 'Description Discovery',
                                    ],
                                'unitPrice'   => 1090,
                            ],
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
                    ],
                ],
            ],
        ];

        $dataValue = [
            '563cae7496da1' => [
                '563cae8a5072d' => [
                    'value' => '563cb15fbf353',
                ],
            ],
        ];

        $result = new Cart(0);
        $resultStep = new CartStep('Forfait de participation', 0);
        $libChoiceWithDescriptionResult = new CartRow('Forfait : Formule de sponsoring des CONFERENCES', 1, 11000);
        $resultStep->addCartRow($libChoiceWithDescriptionResult);
        $result->addCartStep($resultStep);

        $libChoiceWithDescriptionTemplate = [
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

        $libChoiceWithDescriptionData     = [
            'value'    => '563cb15fbf353',
        ];

        $locale = 'fr';
        $cart   = new CartBuilder();

        $libChoiceWithDescription = $this->prophesize(LibChoiceWithDescriptionCart::class);
        $libChoiceWithDescription
            ->prepare($libChoiceWithDescriptionTemplate, $libChoiceWithDescriptionData, $locale)
            ->shouldBeCalled()
            ->willReturn($libChoiceWithDescriptionResult);

        $cart->registerCart('choice_with_description', $libChoiceWithDescription->reveal());

        $cartResult = $cart->create($template, $dataValue, $locale);

        $this->assertEquals($result, $cartResult);
    }
}
