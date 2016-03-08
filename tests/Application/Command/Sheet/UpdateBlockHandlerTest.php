<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\UpdateBlock;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;
use Proximum\Vimeet\Application\Components\Template\Validator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Application\Command\Sheet\UpdateBlockHandler;
use Proximum\Vimeet\Application\Components\Sheet\StateSetter;

class UpdateBlockHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event = new Event();
        $type  = new Type($event);
        $type->setSheetTemplate(
            [
                '563cae566af03' => [
                    'label'         => ['fr' => 'Société', 'en' => 'Company'],
                    'template'      => [
                        '563cb103926e6' => [
                            'type'     => 'lib_text',
                            'required' => true,
                            'private'  => false,
                            'label'    => ['fr' => 'Nom de l\'organisme', 'en' => 'Name']
                        ],
                        '563cb10a524c5' => [
                            'type'     => 'lib_text',
                            'required' => false,
                            'private'  => false,
                            'label'    => ['fr' => 'Adresse', 'en' => 'Address']
                        ],
                        '563cb11d08df1' => [
                            'type'         => 'lib_textarea',
                            'required'     => false,
                            'private'      => false,
                            'translatable' => true,
                            'label'        => ['fr' => 'Mes offres', 'en' => 'My offers'],
                        ],
                    ]
                ],
                '563cae5f48ce2' => [
                    'label'    => ['fr' => 'Offres', 'en' => 'Offers'],
                    'template' => [
                        '563cb11d08df1' => [
                            'type'         => 'lib_textarea',
                            'required'     => false,
                            'private'      => false,
                            'translatable' => true,
                            'label'        => ['fr' => 'Mes offres', 'en' => 'My offers'],
                        ],
                    ]
                ]
            ]
        );

        //Actual
        $sheet = new Sheet(
            $event,
            $type,
            [
                '563cae566af03' => [
                    '563cb103926e6' => 'toto',
                    '563cb10a524c5' => 'toto',
                    '563cb11d08df1' => ['fr' => 'foobar', 'en' => 'foobar_en'],
                ],
                '563cae5f48ce2' => [
                    '563cb11d08df1' => ['fr' => 'foobar'],
                ]
            ],
            [],
            new \DateTime()
        );

        //Expected
        $expected = new Sheet(
            $event,
            $type,
            [
                '563cae566af03' => [
                    '563cb103926e6' => 'titi',
                    '563cb10a524c5' => 'toto',
                    '563cb11d08df1' => ['fr' => 'barfoo', 'en' => 'foobar_en'],
                ],
                '563cae5f48ce2' => [
                    '563cb11d08df1' => ['fr' => 'foobar']
                ]
            ],
            [],
            new \DateTime()
        );

        //Command
        $command       = new UpdateBlock($sheet, '563cae566af03', 'fr');
        $command->data = [
            '563cb103926e6' => 'titi',
            '563cb10a524c5' => 'toto',
            '563cb11d08df1' => 'barfoo',
        ];

        // Dependencies
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $templateFactory = new TemplateFactory();
        $validator       = new Validator($templateFactory);
        $stateSetter     = $this->prophesize(StateSetter::class);

        // Prophecies
        $stateSetter->setState($expected)->shouldBeCalled();
        $sheetRepository->set($expected)->shouldBeCalled();

        // Handler
        $handler = new UpdateBlockHandler($sheetRepository->reveal(), $templateFactory, $validator, $stateSetter->reveal());
        $handler->handle($command);
    }
}
