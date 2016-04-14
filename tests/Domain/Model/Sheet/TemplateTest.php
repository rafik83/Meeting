<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testAddLocale()
    {
        $createdAt = new \DateTime();
        $template  = new Template('My template', [
            'ec74be5e' => [
                'component' => 'object',
                'type'      => 'text',
                'config'    => [
                    'content' => ['fr' => 'Lorem ipsum']
                ],
            ],
            '211b2168' => [
                'component' => 'block',
                'type'      => '8-4',
                'config'    => [
                    [
                        '0aea62b2' => [
                            'component' => 'object',
                            'type'      => 'editable-text',
                            'config'    => [
                                'label'       => ['fr' => 'Titre'],
                                'placeholder' => ['fr' => 'Le titre'],
                                'help'        => ['fr' => 'Ici le titre'],
                                'length'      => 100,
                                'required'    => true,
                            ]
                        ]
                    ],
                    [

                    ]
                ]
            ],
        ], ['fr'], 'fr', $createdAt);

        $expected = new Template('My template', [
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
                'config'    => [
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
            ],
        ], ['fr', 'en'], 'fr', $createdAt);

        $this->assertEquals($expected, $template->addLocale('en'));
    }

    public function testGetFallback()
    {
        $template = new Template('My template', [], ['fr', 'en'], 'fr', new \DateTime());

        $this->assertEquals('fr', $template->getFallback());
        $this->assertEquals('fr', $template->addLocale('de')->getFallback());
    }

    public function testHasLocale()
    {
        $template = new Template('My template', [], ['fr', 'en'], 'fr', new \DateTime());

        $this->assertTrue($template->hasLocale('fr'));
        $this->assertTrue($template->hasLocale('en'));
        $this->assertFalse($template->hasLocale('de'));
        $this->assertTrue($template->addLocale('de')->hasLocale('de'));
    }
}
