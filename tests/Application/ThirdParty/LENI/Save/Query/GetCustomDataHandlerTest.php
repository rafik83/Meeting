<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestDataHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomDataHandler;
use Proximum\Vimeet\Domain\Model\Event;

class GetCustomDataHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sendingRequestDataHandler;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->sendingRequestDataHandler = $this->prophesize(SendingRequestDataHandler::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testHandleWithNoLeniUserId()
    {
        $getCustomDataHandler = new GetCustomDataHandler($this->sendingRequestDataHandler->reveal());


        $this->sendingRequestDataHandler
            ->handle(
                new SendingRequestData(
                    $this->event->reveal(),
                    ['whatever' => 'value', 'EvenementOrigine' => 'API']
                )
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->assertEquals(
            ['whatever' => 'value', 'EvenementOrigine' => 'API'],
            $getCustomDataHandler->handle(
                new GetCustomData(
                    ['whatever' => 'value'],
                    $this->event->reveal()
                )
            )
        );
    }

    public function testHandleWithNoLeniUserIdWithSendingRequestData()
    {
        $getCustomDataHandler = new GetCustomDataHandler($this->sendingRequestDataHandler->reveal());

        $this->sendingRequestDataHandler
            ->handle(
                new SendingRequestData(
                    $this->event->reveal(),
                    ['whatever' => 'value', 'EvenementOrigine' => 'API']
                )
            )
            ->shouldBeCalled()
            ->willReturn(['codeCommunication' => '5W3ORMI3', 'id' => '9A74DF80-1B13-9B68-74A8-1D956F54FECB'])
        ;

        $this->assertEquals(
            [
                'whatever' => 'value',
                'EvenementOrigine' => 'API',
                'SendingRequests' => [
                    'codeCommunication' => '5W3ORMI3',
                    'id' => '9A74DF80-1B13-9B68-74A8-1D956F54FECB',
                ]
            ],
            $getCustomDataHandler->handle(
                new GetCustomData(
                    ['whatever' => 'value'],
                    $this->event->reveal()
                )
            )
        );
    }

    public function testHandleWithLeniUserId()
    {
        $getCustomDataHandler = new GetCustomDataHandler($this->sendingRequestDataHandler->reveal());

        $this->sendingRequestDataHandler
            ->handle(
                new SendingRequestData(
                    $this->event->reveal(),
                    ['Id' => 'GLP971', 'whatever' => 'value']
                )
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->assertEquals(
            ['Id' => 'GLP971', 'whatever' => 'value'],
            $getCustomDataHandler->handle(
                new GetCustomData(
                    ['Id' => 'GLP971', 'whatever' => 'value'],
                    $this->event->reveal()
                )
            )
        );
    }
}
