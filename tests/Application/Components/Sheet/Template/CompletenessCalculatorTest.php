<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\CompletenessCalculator;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use PHPUnit\Framework\TestCase;

class CompletenessCalculatorTest extends TestCase
{
    public static function provideLocales()
    {
        return [
            [
                // Template
                new SheetTemplate('test', [
                    'ec74be5e' => [
                        'component' => 'object',
                        'type'      => 'text',
                        'config'    => [
                            'content' => ['en' => null, 'fr' => 'Lorem ipsum'],
                        ],
                    ],
                    '211b2168' => [
                        'component' => 'block',
                        'type'      => '8-4',
                        'config'    => [],
                        'children'  => [
                            [
                                '0aea62b2' => [
                                    'component' => 'object',
                                    'type'      => 'editable-text',
                                    'config'    => [
                                        'label'       => ['en' => null, 'fr' => 'Titre'],
                                        'placeholder' => ['en' => null, 'fr' => 'Le titre'],
                                        'help'        => ['en' => null, 'fr' => 'Ici le titre'],
                                        'length'      => 100,
                                        'required'    => true,
                                    ]
                                ]
                            ],
                            [

                            ]
                        ]
                    ]
                ], ['fr', 'en'], 'fr', new \DateTime()),
                // Expected
                [
                    'fr' => 100,
                    'en' => 0,
                ]
            ],
            [
                // Template
                new SheetTemplate('test', [
                    'ec74be5e' => [
                        'component' => 'object',
                        'type'      => 'text',
                        'config'    => [
                            'content' => ['en' => null, 'fr' => 'Lorem ipsum'],
                        ],
                    ],
                    '211b2168' => [
                        'component' => 'block',
                        'type'      => '8-4',
                        'config'    => [],
                        'children'  => [
                            [
                                '0aea62b2' => [
                                    'component' => 'object',
                                    'type'      => 'editable-text',
                                    'config'    => [
                                        'label'       => ['en' => null, 'fr' => 'Titre'],
                                        'placeholder' => ['en' => 'The title', 'fr' => 'Le titre'],
                                        'help'        => ['en' => null, 'fr' => 'Ici le titre'],
                                        'length'      => 100,
                                        'required'    => true,
                                    ]
                                ]
                            ],
                            [

                            ]
                        ]
                    ]
                ], ['fr', 'en'], 'fr', new \DateTime()),
                // Expected
                [
                    'fr' => 100,
                    'en' => 25,
                ]
            ],
            [
                // Template
                new SheetTemplate('test', [
                    'ec74be5e' => [
                        'component' => 'object',
                        'type'      => 'text',
                        'config'    => [
                            'content' => ['en' => null, 'fr' => 'Lorem ipsum'],
                        ],
                    ],
                    'ec74be5f' => [
                        'component' => 'object',
                        'type'      => 'text',
                        'config'    => [
                            'content' => ['en' => 'Lorem ipsum', 'fr' => ''],
                        ],
                    ],
                    '211b2168' => [
                        'component' => 'block',
                        'type'      => '8-4',
                        'config'    => [],
                        'children'  => [
                            [
                                '0aea62b2' => [
                                    'component' => 'object',
                                    'type'      => 'editable-text',
                                    'config'    => [
                                        'label'       => ['en' => null, 'fr' => 'Titre'],
                                        'placeholder' => ['en' => 'The title', 'fr' => 'Le titre'],
                                        'help'        => ['en' => null, 'fr' => 'Ici le titre'],
                                        'length'      => 100,
                                        'required'    => true,
                                    ]
                                ]
                            ],
                            [

                            ]
                        ]
                    ]
                ], ['fr', 'en'], 'fr', new \DateTime()),
                // Expected
                [
                    'fr' => 80,
                    'en' => 40,
                ]
            ],
        ];
    }

    /**
     * @param SheetTemplate $template
     * @param array         $expected
     *
     * @dataProvider provideLocales
     */
    public function testCompute(SheetTemplate $template, array $expected)
    {
        $this->assertEquals($expected, (new CompletenessCalculator())->compute($template));
    }
}
