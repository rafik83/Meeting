<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\BuyParticipant;
use Proximum\Vimeet\Application\Command\Sheet\BuyParticipantHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class BuyParticipantHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWhenUserNotExists()
    {
        $event = new Event();
        $type  = new Type();
        $type->setParticipantTemplate([
            'foobar' => [
                'required' => true,
                'private'  => false,
            ]
        ]);
        $type->setPackageTemplate([
            'azerty654321' => [
                'template' => [
                    'azerty12345' => [
                        'required'  => false,
                        'type'      => 'lib_participant',
                        'unitPrice' => 440,
                    ],
                    'ytreza54321' => [
                        'required'  => false,
                        'type'      => "lib_planning",
                        'unitPrice' => 550,
                    ]
                ]
            ]
        ]);

        $sheet = new Sheet($event, $type, [], []);
        $expectedSheet  =  new Sheet($event, $type, [], []);
        $expectedSheet->setPackageData([
            "azerty654321" => [
                "azerty12345" => [
                    "participant" => true,
                    "participant_bought" => 1
                ],
                "ytreza54321" => [
                    "planning" => true,
                    "planning_bought" => 1
                ]
            ]
        ]);

        $expectedUser = new User('test@test.com', '', '', 'fr');
        $owner = false;

        $expectedParticipant = new Participant($sheet, $expectedUser, ['foobar' => 'barfoo'], $owner);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn(null);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $sheetRespository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRespository->set($expectedSheet)->shouldBeCalled();

        $buyParticipant = new BuyParticipant($sheet, 'fr');
        $buyParticipant->participantData['email']         = 'test@test.com';
        $buyParticipant->participantData['data']          = ['foobar' => 'barfoo'];
        $buyParticipant->participantBuyOption['planning'] = true;

        $handler = new BuyParticipantHandler($userRepository->reveal(), $participantRepository->reveal(), $sheetRespository->reveal());
        $handler->handle($buyParticipant);
    }
}
