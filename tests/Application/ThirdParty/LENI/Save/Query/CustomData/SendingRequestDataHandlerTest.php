<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Query\CustomData;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestDataHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class SendingRequestDataHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
    }

    public function testHandleWithLeniUserIdPresent()
    {
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SENDING_REQUEST)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $handler = new SendingRequestDataHandler($this->extraParameterRepository->reveal());

        $data = [
            LeniConstants::LENI_COL_USER_ID => '123456789-0987654321',
        ];

        $result = $handler->handle(
            new SendingRequestData(
                $this->event->reveal(),
                $this->user->reveal(),
                $data,
                false
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
                $this->user->reveal(),
                [],
                false
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
                $this->user->reveal(),
                [],
                false
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
                $this->user->reveal(),
                [],
                false
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

    public function testHandleWhenSheetIsNotValidated()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->willReturn("{\"sending_request_sheet_is_validated\":[{\"codeCommunication\": \"5W3ORMI3\", \"id\": \"9A74DF80-1B13-9B68-74A8-1D956F54FECB\"}]}");

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SENDING_REQUEST)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $handler = new SendingRequestDataHandler($this->extraParameterRepository->reveal());

        $result = $handler->handle(
            new SendingRequestData(
                $this->event->reveal(),
                $this->user->reveal(),
                [],
                false // sheet is not validated
            )
        );

        $this->assertEquals([], $result);
    }

    public function testHandleWhenSheetIsValidated()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->willReturn("{\"sending_request_sheet_is_validated\":[{\"codeCommunication\": \"5W3ORMI3\", \"id\": \"9A74DF80-1B13-9B68-74A8-1D956F54FECB\"}]}");

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SENDING_REQUEST)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $handler = new SendingRequestDataHandler($this->extraParameterRepository->reveal());

        $result = $handler->handle(
            new SendingRequestData(
                $this->event->reveal(),
                $this->user->reveal(),
                [],
                true // sheet is validated
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

    public function testHandleWhenSheetIsValidatedButNoCorrespondingSendingRequest()
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
                $this->user->reveal(),
                ['Id' => '123456789-0987654321'], // has already an Id
                true // sheet is validated
            )
        );

        $this->assertEquals([], $result);
    }
}
