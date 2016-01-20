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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Application\Command\Sheet\UpdateBlockHandler;

class UpdateBlockHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event = new Event();
        $type  = new Type($event);
        $type->setSheetTemplate([
            '563cae566af03' => [
                'label' => ['fr' => 'Société', 'en' => 'Company'],
                'template' => [
                        '563cb103926e6' => [
                                'type' => 'lib_organisation',
                                'required=> true',
                                'private' => 'false',
                                'label'=> [
                                    'fr' => "Nom de l'organisme", 'en'=> "Name"
                                ]
                        ],
                        '563cb10a524c5' => [
                                'type' => 'lib_text', 'required' => 'false', 'private' => 'false',
                                'label' => [
                                    'fr' => "Adresse", 'en' => "Address"
                                ]
                        ]
                ],
            '563cae5f48ce2' => [
                'label'=> ['fr' => "Offres", 'en' => "Offers"],
                'template' => [
                        '563cb11d08df1' => [
                                'type' => 'lib_textarea',
                                'required' => 'false',
                                'private' => 'false',
                                'label' => ['fr' => "Mes offres", 'en' => "My offers" ]
                        ],
                ]
        ]]]);

        //Actual
        $sheet = new Sheet($event, $type, [
            '563cae566af03' => [
                '563cb103926e6' => 'toto',
                '563cb10a524c5' => 'toto'
            ],
            '563cae5f48ce2' => [
                'cb11d08df1' => 'foobar'
            ]
        ], []);

        //Expected
        $expected = new Sheet($event, $type, [
            '563cae566af03' => [
                '563cb103926e6' => 'titi',
                '563cb10a524c5' => 'toto'
            ],
            '563cae5f48ce2' => [
                'cb11d08df1' => 'foobar'
            ]
        ], []);


        //Command
        $command = new UpdateBlock($sheet, '563cae566af03');
        $command->data = [
            '563cb103926e6' => 'titi',
            '563cb10a524c5' => 'toto'
        ];

        //Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($expected)->shouldBeCalled();

        //Handler
        $handler = new UpdateBlockHandler($sheetRepository->reveal());
        $handler->handle($command);
    }

}
