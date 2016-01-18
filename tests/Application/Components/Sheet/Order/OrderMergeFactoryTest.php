<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Order\OrderMerge;
use Proximum\Vimeet\Application\Components\Sheet\Order\OrderMergeFactory;
use Proximum\Vimeet\Application\Components\Sheet\Order\OrderViewFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class OrderMergeFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        // Context
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], []);

        // Orders to merge
        $orders = [
            $this->createOrder($sheet, [
                '563cae7496da1' => [
                    'label'       => ['fr' => 'Forfait de participation', 'en' => 'Participation package'],
                    'description' => ['fr' => 'Description de l\'étape en français', 'en' => 'Step Description in english'],
                    'template'    => [
                        'cae8a5072d' => [
                            'label'       => ['fr' => 'Forfait', 'en' => 'Package'],
                            'description' => ['fr' => 'Description forfait fr', 'en' => 'Description forfait en'],
                            'type'        => 'choice_with_description',
                            'required'    => true,
                            'choices'     => [
                                'cb1257722e' => [
                                    'label'       => ['fr' => 'Formule Exposant', 'en' => 'Exhibitors package'],
                                    'description' => ['fr' => 'Description', 'en' => 'Description'],
                                    'unitPrice'   => 2190,
                                ],
                                '563cb1546dd70' => [
                                    'label'       => ['fr' => 'Formule Gold', 'en' => 'Gold package'],
                                    'description' => ['fr' => 'Description', 'en' => 'Description'],
                                    'unitPrice'   => 5990,
                                ],
                            ],
                        ],
                    ],
                ],
            ], [
                '563cae7496da1' => [
                    'cae8a5072d' => [
                        'value' => 'cae8a5072d',
                    ]
                ]
            ]),
            $this->createOrder($sheet, [
                '56615ef1b3b7d' => [
                    'label'       => ['fr' => 'Stands et équipements', 'en' => 'Stands et équipements en anglais'],
                    'description' => ['fr' => 'Description - Stands et équipements', 'en' => 'Description - Stands et équipements en anglais'],
                    'template'    => [
                        '56615fbd30518' => [
                            'label'       => ['fr' => 'Stand 4m²', 'en' => 'Stand 4m²'],
                            'description' => ['fr' => '', 'en' => ''],
                            'type'        => 'lib_option',
                            'unitPrice'   => 200,
                            'quantity'    => ['min' => 0, 'max' => 2],
                            'includedIn'  => ['563cae7496da1.563cae8a5072d.563cb1257722e' => null],
                        ],
                        '56615feba09a4' => [
                            'label'       => ['fr' => 'Stand 6m²', 'en' => 'Stand 6m²'],
                            'description' => ['fr' => '', 'en' => ''],
                            'type'        => 'lib_option',
                            'unitPrice'   => 300,
                            'quantity'    => ['min' => 0, 'max' => 2],
                            'includedIn'  => ['563cae7496da1.563cae8a5072d.563cb13a4d3de' => null]
                        ]
                    ]
                ]
            ], [
                '56615ef1b3b7d' => [
                    '56615fbd30518' => ['value' => true, 'quantity' => 2],
                    '56615feba09a4' => ['value' => true, 'quantity' => 1]
                ]
            ]),
            $this->createOrder($sheet, [
                '56615ef1b3b7d' => [
                    'label'       => ['fr' => 'Stands et équipements', 'en' => 'Stands et équipements en anglais'],
                    'description' => ['fr' => 'Description - Stands et équipements', 'en' => 'Description - Stands et équipements en anglais'],
                    'template'    => [
                        '56615fbd30518' => [
                            'label'       => ['fr' => 'Stand 4m²', 'en' => 'Stand 4m²'],
                            'description' => ['fr' => '', 'en' => ''],
                            'type'        => 'lib_option',
                            'unitPrice'   => 200,
                            'quantity'    => ['min' => 0, 'max' => 2],
                            'includedIn'  => ['563cae7496da1.563cae8a5072d.563cb1257722e' => null],
                        ],
                        '56615feba09a4' => [
                            'label'       => ['fr' => 'Stand 6m²', 'en' => 'Stand 6m²'],
                            'description' => ['fr' => '', 'en' => ''],
                            'type'        => 'lib_option',
                            'unitPrice'   => 300,
                            'quantity'    => ['min' => 0, 'max' => 2],
                            'includedIn'  => ['563cae7496da1.563cae8a5072d.563cb13a4d3de' => null]
                        ]
                    ]
                ]
            ], [
                '56615ef1b3b7d' => [
                    '56615fbd30518' => ['value' => true, 'quantity' => 1],
                    '56615feba09a4' => ['value' => false, 'quantity' => 0]
                ]
            ]),
        ];

        // Expected
        $template = [
            '563cae7496da1' => [
                'label'       => ['fr' => 'Forfait de participation', 'en' => 'Participation package'],
                'description' => ['fr' => 'Description de l\'étape en français', 'en' => 'Step Description in english'],
                'template'    => [
                    'cae8a5072d' => [
                        'label'       => ['fr' => 'Forfait', 'en' => 'Package'],
                        'description' => ['fr' => 'Description forfait fr', 'en' => 'Description forfait en'],
                        'type'        => 'choice_with_description',
                        'required'    => true,
                        'choices'     => [
                            'cb1257722e' => [
                                'label'       => ['fr' => 'Formule Exposant', 'en' => 'Exhibitors package'],
                                'description' => ['fr' => 'Description', 'en' => 'Description'],
                                'unitPrice'   => 2190,
                            ],
                            '563cb1546dd70' => [
                                'label'       => ['fr' => 'Formule Gold', 'en' => 'Gold package'],
                                'description' => ['fr' => 'Description', 'en' => 'Description'],
                                'unitPrice'   => 5990,
                            ],
                        ],
                    ],
                ],
            ],
            '56615ef1b3b7d' => [
                'label'       => ['fr' => 'Stands et équipements', 'en' => 'Stands et équipements en anglais'],
                'description' => ['fr' => 'Description - Stands et équipements', 'en' => 'Description - Stands et équipements en anglais'],
                'template'    => [
                    '56615fbd30518' => [
                        'label'       => ['fr' => 'Stand 4m²', 'en' => 'Stand 4m²'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 200,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'includedIn'  => ['563cae7496da1.563cae8a5072d.563cb1257722e' => null],
                    ],
                    '56615feba09a4' => [
                        'label'       => ['fr' => 'Stand 6m²', 'en' => 'Stand 6m²'],
                        'description' => ['fr' => '', 'en' => ''],
                        'type'        => 'lib_option',
                        'unitPrice'   => 300,
                        'quantity'    => ['min' => 0, 'max' => 2],
                        'includedIn'  => ['563cae7496da1.563cae8a5072d.563cb13a4d3de' => null]
                    ]
                ]
            ]
        ];
        $data     = [
            '563cae7496da1' => [
                'cae8a5072d' => [
                    'value' => 'cae8a5072d',
                ]
            ],
            '56615ef1b3b7d' => [
                '56615fbd30518' => ['value' => true, 'quantity' => 3],
                '56615feba09a4' => ['value' => true, 'quantity' => 1]
            ]
        ];

        // Expected
        $expected = new OrderMerge([], 20);

        // Mock
        $orderViewFactory = $this->prophesize(OrderViewFactory::class);
        $orderViewFactory->createGroupsFromArray($template, $data, 'fr')->shouldBeCalled()->willReturn([]);

        // Merge
        $merger = new OrderMergeFactory($orderViewFactory->reveal());

        $this->assertEquals($expected, $merger->merge($orders, 20, 'fr'));
    }

    private function createOrder(Sheet $sheet, array $template, array $data)
    {
        return new Order($sheet, Order::STATE_PAID, '', $data, $template, [], [], new \DateTime(), '');
    }
}
