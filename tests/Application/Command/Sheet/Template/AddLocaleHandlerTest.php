<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\AddLocale;
use Proximum\Vimeet\Application\Command\Sheet\Template\AddLocaleHandler;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\TemplateException;

class AddLocaleHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $createdAt = new \DateTime();

        $template = new SheetTemplate('My template', [
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
        ], ['fr'], $createdAt);

        $expected = new SheetTemplate('My template', [
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
        ], ['fr', 'en'], $createdAt);

        $templateRepository = $this->prophesize(TemplateRepositoryInterface::class);
        $templateRepository->set($expected)->shouldBeCalled();

        $command = new AddLocale($template);
        $command->locale = 'en';

        $handler = new AddLocaleHandler($templateRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleHasLocale()
    {
        $this->expectException(TemplateException::class);

        $template = new SheetTemplate('My template', [
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
        ], ['fr'], new \DateTime());

        $templateRepository = $this->prophesize(TemplateRepositoryInterface::class);
        $templateRepository->set()->shouldNotBeCalled();

        $command         = new AddLocale($template);
        $command->locale = 'fr';

        $handler = new AddLocaleHandler($templateRepository->reveal());
        $handler->handle($command);
    }
}
