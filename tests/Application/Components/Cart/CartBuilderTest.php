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
use Proximum\Vimeet\Application\Components\Cart\CartRecap;
use Proximum\Vimeet\Application\Components\Cart\CartRow;
use Proximum\Vimeet\Application\Components\Cart\Carts\LibChoiceWithDescriptionCart;
use Proximum\Vimeet\Application\Components\Cart\CartStep;
use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceWithDescriptionProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibOptionProduct;
use Proximum\Vimeet\Application\Components\Product\Step;
use Proximum\Vimeet\Application\Components\Product\Template;

class CartBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCartBuilderWithEmptyTemplateAndData()
    {
        $dataValue  = [];
        $locale     = 'fr';
        $result     = new CartRecap(0);
        $cart       = new CartBuilder();
        $template   = new Template();

        $cartResult = $cart->generate($template, $dataValue, $locale);

        $this->assertEquals($result, $cartResult);
    }

    public function testCartBuilder()
    {
        $template = $this->getTemplate();

        $dataValue = [
            '563cae7496da1' => [
                '563cae8a5072d' => [
                    'value' => '563cb13a4d3de',
                ],
            ],
        ];
        $libChoiceData = [
            'value' => '563cb13a4d3de',
        ];

        $result = new CartRecap(0);
        $resultStep = new CartStep('Forfait de participation', 0);
        $libChoiceWithDescriptionResult = new CartRow('Forfait : Formule de sponsoring des CONFERENCES', 1, 11000);
        $includeCartRow = new CartRow(
            'Test include',
            1,
            0
        );
        $libChoiceWithDescriptionResult->addInclude($includeCartRow);

        $resultStep->addCartRow($libChoiceWithDescriptionResult);
        $result->addCartStep($resultStep);


        $locale = 'fr';
        $cart   = new CartBuilder();

        $libChoiceWithDescription = $this->prophesize(LibChoiceWithDescriptionCart::class);
        $libChoiceWithDescription
            ->prepare($template['product1'], $libChoiceData, $locale)
            ->shouldBeCalled()
            ->willReturn($libChoiceWithDescriptionResult);

        $cart->registerCart('choice_with_description', $libChoiceWithDescription->reveal());

        $cartResult = $cart->generate($template['template'], $dataValue, $locale);

        $this->assertEquals($result, $cartResult);
    }

    private function getTemplate()
    {
        $expectedTemplate   = new Template();
        $expectedStep       = new Step('563cae7496da1');
        $expectedStepOption = [
            'label'       => ['fr' => 'Forfait de participation'],
            'description' => [
                'fr' => 'Description de l\'étape en français'],
            'template'    => [
                '563cae8a5072d' => [
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
                ],
            ]
        ];
        $expectedStep->setOptions($expectedStepOption);
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

        $expectedStep2 = new Step('56407fa453f4e');
        $expectedStep2Option = [
            'label'       => ['fr' => "Options proposées pour optimiser votre participation"],
            'description' => ['fr' => "Publicité et prommotion"],
            'template' => [
                '56615a0ab3f9f' => [
                    'label' => [
                        'fr' => "Affichage logo sur site événement et supports de communication",
                    ],
                    'description' => [
                        'fr' => "test",
                    ],
                    'type' => "lib_option",
                    'unitPrice' => 200,
                    'includedIn' => [
                        '563cae7496da1.563cae8a5072d.563cb13a4d3de' => null,
                        '563cae7496da1.563cae8a5072d.563cb1546dd70' => null,
                    ],
                ],
            ],
        ];

        $expectedProduct2 = new LibOptionProduct('56615a0ab3f9f');
        $expectedProduct2Option = [
            'label' => [
                'fr' => "Affichage logo sur site événement et supports de communication",
            ],
            'description' => [
                'fr' => "test",
            ],
            'type' => "lib_option",
            'unitPrice' => 200,
            'includedIn' => [
                '563cae7496da1.563cae8a5072d.563cb13a4d3de' => null,
                '563cae7496da1.563cae8a5072d.563cb1546dd70' => null,
            ],
        ];

        $expectedProduct2->setOptions($expectedProduct2Option);
        $expectedStep2->setOptions($expectedStep2Option);
        $expectedStep2->addProduct($expectedProduct2);

        $expectedChoiceProduct2->setOptions($expectedChoiceProduct2Option);
        $expectedChoiceProduct2->setChoiceParent($expectedProduct);
        $expectedChoiceProduct->setChoiceParent($expectedProduct);
        $expectedProduct->addChoice($expectedChoiceProduct);
        $expectedProduct->addChoice($expectedChoiceProduct2);
        $expectedStep->addProduct($expectedProduct);
        $expectedTemplate->addStep($expectedStep);
        $expectedTemplate->addStep($expectedStep2);

        $expectedProduct2->including($expectedChoiceProduct, $expectedProduct2, null);
        $expectedProduct2->including($expectedChoiceProduct2, $expectedProduct2, null);

        return [
            'template' => $expectedTemplate,
            'step1' => $expectedStep,
            'step2' => $expectedStep2,
            'product1' => $expectedProduct,
            'product2' => $expectedProduct2,
            'choiceProduct1' => $expectedChoiceProduct,
            'choiceProduct2' => $expectedChoiceProduct,
        ];
    }
}
