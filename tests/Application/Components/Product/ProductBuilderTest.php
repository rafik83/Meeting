<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Product;

use Proximum\Vimeet\Application\Components\Product\ProductBuilder;
use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceWithDescriptionProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibOptionProduct;
use Proximum\Vimeet\Application\Components\Product\Step;
use Proximum\Vimeet\Application\Components\Product\Template;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromTypeEmpty()
    {
        // In
        $event = new Event();
        $type  = new Type($event);
        $type->setPackageTemplate([]);

        // Expected
        $template = new Template();

        // Builder
        $productBuilder = new ProductBuilder();

        // Assert
        $this->assertEquals($template, $productBuilder->createFromType($type));
    }

    public function testCreateFromTypeWithOneStep()
    {
        // In
        $event = new Event();
        $type  = new Type($event);
        $type->setPackageTemplate([
            'step1' => [
                'label'       => ['fr' => 'Etape 1', 'en' => 'Step 1'],
                'description' => ['fr' => 'Lorem ipsum fr', 'en' => 'Lorem ipsum en'],
                'position'    => 1,
                'template'    => [
                    'option1' => [
                        'label'       => ['fr' => 'Option 1', 'en' => 'Option 1'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 300,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'position'    => 1,
                    ],
                    'option2' => [
                        'label'       => ['fr' => 'Option 2', 'en' => 'Option 2'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 300,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'position'    => 2,
                    ],
                    'option3' => [
                        'label'       => ['fr' => 'Option 3', 'en' => 'Option 3'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 300,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'position'    => 3,
                    ],
                ]
            ]
        ]);

        // Expected
        $template = new Template();

        $step1 = new Step('step1');
        $step1->setOptions([
            'label'       => ['fr' => 'Etape 1', 'en' => 'Step 1'],
            'description' => ['fr' => 'Lorem ipsum fr', 'en' => 'Lorem ipsum en'],
            'position'    => 1,
            'template'    => [
               'option1' => [
                   'label'       => ['fr' => 'Option 1', 'en' => 'Option 1'],
                   'description' => ['fr' => '', 'en' => ''],
                   'type'        => 'lib_option',
                   'unitPrice'   => 300,
                   'quantity'    => ['min' => 0, 'max' => 2],
                   'position'    => 1,
               ],
               'option2' => [
                   'label'       => ['fr' => 'Option 2', 'en' => 'Option 2'],
                   'description' => ['fr' => '', 'en' => ''],
                   'type'        => 'lib_option',
                   'unitPrice'   => 300,
                   'quantity'    => ['min' => 0, 'max' => 2],
                   'position'    => 2,
               ],
               'option3' => [
                   'label'       => ['fr' => 'Option 3', 'en' => 'Option 3'],
                   'description' => ['fr' => '', 'en' => ''],
                   'type'        => 'lib_option',
                   'unitPrice'   => 300,
                   'quantity'    => ['min' => 0, 'max' => 2],
                   'position'    => 3,
               ],
            ]
        ]);
        $template->addStep($step1);

        $product1 = new LibOptionProduct('option1');
        $product1->setOptions([
            'label'       => ['fr' => 'Option 1', 'en' => 'Option 1'],
            'description' => ['fr' => '', 'en' => ''],
            'type'        => 'lib_option',
            'unitPrice'   => 300,
            'quantity'    => ['min' => 0, 'max' => 2],
            'position'    => 1,
        ]);
        $step1->addProduct($product1);

        $product2 = new LibOptionProduct('option2');
        $product2->setOptions([
            'label'       => ['fr' => 'Option 2', 'en' => 'Option 2'],
            'description' => ['fr' => '', 'en' => ''],
            'type'        => 'lib_option',
            'unitPrice'   => 300,
            'quantity'    => ['min' => 0, 'max' => 2],
            'position'    => 2,
        ]);
        $step1->addProduct($product2);

        $product3 = new LibOptionProduct('option3');
        $product3->setOptions([
            'label'       => ['fr' => 'Option 3', 'en' => 'Option 3'],
            'description' => ['fr' => '', 'en' => ''],
            'type'        => 'lib_option',
            'unitPrice'   => 300,
            'quantity'    => ['min' => 0, 'max' => 2],
            'position'    => 3,
        ]);
        $step1->addProduct($product3);

        // Builder
        $productBuilder = new ProductBuilder();

        // Assert
        $this->assertEquals($template, $productBuilder->createFromType($type));
    }

    public function testCreateFromTypeWithTwoSteps()
    {
        // In
        $event = new Event();
        $type  = new Type($event);
        $type->setPackageTemplate([
            'step1' => [
                'label'       => ['fr' => 'Etape 1', 'en' => 'Step 1'],
                'description' => ['fr' => 'Lorem ipsum fr', 'en' => 'Lorem ipsum en'],
                'position'    => 10,
                'template'    => [
                    'option1' => [
                        'label'       => ['fr' => 'Option 1', 'en' => 'Option 1'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 300,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'position'    => 100,
                    ],
                ],
            ],
            'step2' => [
                'label'       => ['fr' => 'Etape 1', 'en' => 'Step 1'],
                'description' => ['fr' => 'Lorem ipsum fr', 'en' => 'Lorem ipsum en'],
                'position'    => 20,
                'template'    => [
                    'option2' => [
                        'label'       => ['fr' => 'Option 2', 'en' => 'Option 2'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 300,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'position'    => 9,
                    ],
                    'option3' => [
                        'label'       => ['fr' => 'Option 3', 'en' => 'Option 3'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 300,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'position'    => 99,
                    ],
                ],
            ],
        ]);

        // Expected
        $template = new Template();

        $step1 = new Step('step1');
        $step1->setOptions([
            'label'       => ['fr' => 'Etape 1', 'en' => 'Step 1'],
            'description' => ['fr' => 'Lorem ipsum fr', 'en' => 'Lorem ipsum en'],
            'position'    => 10,
            'template'    => [
                'option1' => [
                    'label'       => ['fr' => 'Option 1', 'en' => 'Option 1'],
                    'description' => ['fr' => '', 'en' => ''],
                    'type'        => 'lib_option',
                    'unitPrice'   => 300,
                    'quantity'    => ['min' => 0, 'max' => 2],
                    'position'    => 100,
                ],
            ]
        ]);
        $template->addStep($step1);

        $product1 = new LibOptionProduct('option1');
        $product1->setOptions([
            'label'       => ['fr' => 'Option 1', 'en' => 'Option 1'],
            'description' => ['fr' => '', 'en' => ''],
            'type'        => 'lib_option',
            'unitPrice'   => 300,
            'quantity'    => ['min' => 0, 'max' => 2],
            'position'    => 100,
        ]);
        $step1->addProduct($product1);

        $step2 = new Step('step2');
        $step2->setOptions([
            'label'       => ['fr' => 'Etape 1', 'en' => 'Step 1'],
            'description' => ['fr' => 'Lorem ipsum fr', 'en' => 'Lorem ipsum en'],
            'position'    => 20,
            'template'    => [
               'option2' => [
                   'label'       => ['fr' => 'Option 2', 'en' => 'Option 2'],
                   'description' => ['fr' => '', 'en' => ''],
                   'type'        => 'lib_option',
                   'unitPrice'   => 300,
                   'quantity'    => ['min' => 0, 'max' => 2],
                   'position'    => 9,
               ],
               'option3' => [
                   'label'       => ['fr' => 'Option 3', 'en' => 'Option 3'],
                   'description' => ['fr' => '', 'en' => ''],
                   'type'        => 'lib_option',
                   'unitPrice'   => 300,
                   'quantity'    => ['min' => 0, 'max' => 2],
                   'position'    => 99,
               ],
            ]
        ]);
        $template->addStep($step2);

        $product2 = new LibOptionProduct('option2');
        $product2->setOptions([
            'label'       => ['fr' => 'Option 2', 'en' => 'Option 2'],
            'description' => ['fr' => '', 'en' => ''],
            'type'        => 'lib_option',
            'unitPrice'   => 300,
            'quantity'    => ['min' => 0, 'max' => 2],
            'position'    => 9,
        ]);
        $step2->addProduct($product2);

        $product3 = new LibOptionProduct('option3');
        $product3->setOptions([
            'label'       => ['fr' => 'Option 3', 'en' => 'Option 3'],
            'description' => ['fr' => '', 'en' => ''],
            'type'        => 'lib_option',
            'unitPrice'   => 300,
            'quantity'    => ['min' => 0, 'max' => 2],
            'position'    => 99,
        ]);
        $step2->addProduct($product3);

        // Builder
        $productBuilder = new ProductBuilder();

        // Assert
        $this->assertEquals($template, $productBuilder->createFromType($type));
    }

    public function testWithEmptyPackage()
    {
        $expectedTemplate = new Template();
        $productBuilder   = new ProductBuilder();
        $packageTemplate  = [];
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $type->setPackageTemplate($packageTemplate);

        $template = $productBuilder->createFromSheet($sheet);
        $this->assertEquals($expectedTemplate, $template);
    }

    public function testProductBuilderWithoutIncludedIn()
    {
        $expectedTemplate   = new Template();
        $expectedStep       = new Step('563cae7496da1');
        $expectedStepOption = [
            'label'       => ['fr' => 'Forfait de participation'],
            'description' => ['fr' => 'Description de l\'étape en français'],
            'position'    => 1,
            'template'    => [
                '563cae8a5072d' => [
                    'label'       => ['fr' => 'Forfait'],
                    'description' => ['fr' => 'Description forfait fr'],
                    'type'        => 'choice_with_description',
                    'required'    => true,
                    'position'    => 1,
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
                            'position'    => 1,
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
                            'position'    => 2,
                        ],
                    ],
                ],
            ]
        ];
        $expectedStep->setOptions($expectedStepOption);
        $expectedProduct  = new LibChoiceWithDescriptionProduct('563cae8a5072d');
        $expectedProductOption = [
            'label'       => ['fr' => 'Forfait'],
            'description' => ['fr' => 'Description forfait fr'],
            'type'        => 'choice_with_description',
            'required'    => true,
            'position'    => 1,
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
                    'position'    => 1,
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
                    'position'    => 2,
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
            'position'    => 1,
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
            'position'    => 2,
        ];
        $expectedChoiceProduct2->setOptions($expectedChoiceProduct2Option);
        $expectedChoiceProduct2->setChoiceParent($expectedProduct);
        $expectedChoiceProduct->setChoiceParent($expectedProduct);
        $expectedProduct->addChoice($expectedChoiceProduct);
        $expectedProduct->addChoice($expectedChoiceProduct2);
        $expectedStep->addProduct($expectedProduct);
        $expectedTemplate->addStep($expectedStep);


        $productBuilder   = new ProductBuilder();
        $packageTemplate = [
            '563cae7496da1' => [
                'label'       => [
                    'fr' => 'Forfait de participation'
                ],
                'description' => [
                    'fr' => 'Description de l\'étape en français'
                ],
                'position'    => 1,
                'template'    => [
                    '563cae8a5072d' => [
                        'label'       =>
                            ['fr' => 'Forfait'],
                        'description' =>
                            ['fr' => 'Description forfait fr'],
                        'type'        => 'choice_with_description',
                        'required'    => true,
                        'position'    => 1,
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
                                'position'    => 1,
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
                                'position'    => 2,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $type->setPackageTemplate($packageTemplate);

        $template = $productBuilder->createFromSheet($sheet);
        $this->assertEquals($expectedTemplate, $template);
    }

    public function testProductBuilderWithIncludedIn()
    {
        $expectedTemplate   = new Template();
        $expectedStep       = new Step('563cae7496da1');
        $expectedStepOption = [
            'label'       => ['fr' => 'Forfait de participation'],
            'description' => [
                'fr' => 'Description de l\'étape en français'
            ],
            'position'    => 1,
            'template'    => [
                '563cae8a5072d' => [
                    'label'       =>
                        ['fr' => 'Forfait'],
                    'description' =>
                        ['fr' => 'Description forfait fr'],
                    'type'        => 'choice_with_description',
                    'required'    => true,
                    'position'    => 1,
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
                            'position'    => 1,
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
                            'position'    => 2,
                        ],
                    ],
                ],
            ]
        ];
        $expectedStep->setOptions($expectedStepOption);
        $expectedProduct  = new LibChoiceWithDescriptionProduct('563cae8a5072d');
        $expectedProductOption = [
            'label'       => ['fr' => 'Forfait'],
            'description' => ['fr' => 'Description forfait fr'],
            'type'        => 'choice_with_description',
            'required'    => true,
            'position'    => 1,
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
                    'position'    => 1,
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
                    'position'    => 2,
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
            'position'    => 1,
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
            'position'    => 2,
        ];

        $expectedStep2 = new Step('56407fa453f4e');
        $expectedStep2Option = [
            'label'       => ['fr' => "Options proposées pour optimiser votre participation"],
            'description' => ['fr' => "Publicité et prommotion"],
            'position'    => 1,
            'template'    => [
                '56615a0ab3f9f' => [
                    'label'       => [
                        'fr' => "Affichage logo sur site événement et supports de communication",
                    ],
                    'description' => [
                        'fr' => "test",
                    ],
                    'type'        => "lib_option",
                    'unitPrice'   => 200,
                    'includedIn'  => [
                        '563cae7496da1.563cae8a5072d.563cb13a4d3de' => null,
                        '563cae7496da1.563cae8a5072d.563cb1546dd70' => null,
                    ],
                    'position'    => 1,
                ],
            ],
        ];

        $expectedProduct2 = new LibOptionProduct('56615a0ab3f9f');
        $expectedProduct2Option = [
            'label'       => [
                'fr' => "Affichage logo sur site événement et supports de communication",
            ],
            'description' => [
                'fr' => "test",
            ],
            'type'        => "lib_option",
            'unitPrice'   => 200,
            'includedIn'  => [
                '563cae7496da1.563cae8a5072d.563cb13a4d3de' => null,
                '563cae7496da1.563cae8a5072d.563cb1546dd70' => null,
            ],
            'position'    => 1,
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


        $productBuilder   = new ProductBuilder();
        $packageTemplate = [
            '563cae7496da1' => [
                'label'       => [
                    'fr' => 'Forfait de participation'
                ],
                'description' => [
                    'fr' => 'Description de l\'étape en français'
                ],
                'position'    => 1,
                'template'    => [
                    '563cae8a5072d' => [
                        'label'       =>
                            ['fr' => 'Forfait'],
                        'description' =>
                            ['fr' => 'Description forfait fr'],
                        'type'        => 'choice_with_description',
                        'required'    => true,
                        'position'    => 1,
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
                                'position'    => 1,
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
                                'position'    => 2,
                            ],
                        ],
                    ],
                ],
            ],
            '56407fa453f4e' => [
                'label'       => ['fr' => "Options proposées pour optimiser votre participation"],
                'description' => ['fr' => "Publicité et prommotion"],
                'position'    => 1,
                'template'    => [
                    '56615a0ab3f9f' => [
                        'label'       => [
                            'fr' => "Affichage logo sur site événement et supports de communication",
                        ],
                        'description' => [
                            'fr' => "test",
                        ],
                        'type'        => "lib_option",
                        'unitPrice'   => 200,
                        'includedIn'  => [
                            '563cae7496da1.563cae8a5072d.563cb13a4d3de' => null,
                            '563cae7496da1.563cae8a5072d.563cb1546dd70' => null,
                        ],
                        'position'    => 1,
                    ],
                ],
            ]
        ];

        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $type->setPackageTemplate($packageTemplate);

        $template = $productBuilder->createFromSheet($sheet);
        $this->assertEquals($expectedTemplate, $template);
    }
}
