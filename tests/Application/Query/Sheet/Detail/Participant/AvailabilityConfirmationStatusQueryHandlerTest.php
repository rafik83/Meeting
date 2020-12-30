<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\Detail\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AvailabilityConfirmationStatusQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AvailabilityConfirmationStatusQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AvailabilityConfirmedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AvailabilityNotConfirmedView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class AvailabilityConfirmationStatusQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $extraData = $this->prophesize(ExtraData::class);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), Type::AVAILABILITY_CONFIRMATION, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;

        $handler = new AvailabilityConfirmationStatusQueryHandler($extraDataRepository->reveal());
        $result = $handler->handle(new AvailabilityConfirmationStatusQuery($event->reveal(), $user->reveal()));

        $this->assertEquals(new AvailabilityConfirmedView(), $result);
    }

    public function testHandleNotConfirmed()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), Type::AVAILABILITY_CONFIRMATION, $user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $handler = new AvailabilityConfirmationStatusQueryHandler($extraDataRepository->reveal());
        $result = $handler->handle(new AvailabilityConfirmationStatusQuery($event->reveal(), $user->reveal()));

        $this->assertEquals(new AvailabilityNotConfirmedView(), $result);
    }
}
