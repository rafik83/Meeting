<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Query\CustomData;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestDataHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class SendingRequestDataHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testHandleWithLeniUserIdPresent()
    {
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SENDING_REQUEST)
            ->shouldNotBeCalled()
        ;

        $handler = new SendingRequestDataHandler($this->extraParameterRepository->reveal());

        $data = [
            LeniConstants::LENI_COL_USER_ID => '123456789-0987654321',
        ];

        $result = $handler->handle(
            new SendingRequestData(
                $this->event->reveal(),
                $data
            )
        );

        $this->assertEquals([], $result);
    }

    public function testHandleWithoutLeniUserIdButWithoutExtraParam()
    {
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SENDING_REQUEST)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $handler = new SendingRequestDataHandler($this->extraParameterRepository->reveal());

        $result = $handler->handle(
            new SendingRequestData(
                $this->event->reveal(),
                []
            )
        );

        $this->assertEquals([], $result);
    }

    public function testHandleWithoutLeniUserIdWithExtraParamButNoParameterSendingRequestNewUser()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->willReturn('[]');

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SENDING_REQUEST)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $handler = new SendingRequestDataHandler($this->extraParameterRepository->reveal());

        $result = $handler->handle(
            new SendingRequestData(
                $this->event->reveal(),
                []
            )
        );

        $this->assertEquals([], $result);
    }

    public function testHandleWithoutLeniUserIdWithExtraParam()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->willReturn("{\"sending_request_new_user\":[{\"codeCommunication\": \"5W3ORMI3\", \"id\": \"9A74DF80-1B13-9B68-74A8-1D956F54FECB\"}]}");

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SENDING_REQUEST)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $handler = new SendingRequestDataHandler($this->extraParameterRepository->reveal());

        $result = $handler->handle(
            new SendingRequestData(
                $this->event->reveal(),
                []
            )
        );

        $this->assertEquals(
            [
                [
                    'codeCommunication' => '5W3ORMI3',
                    'id' => '9A74DF80-1B13-9B68-74A8-1D956F54FECB',
                ]
            ],
            $result
        );
    }
}
