<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Availability;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\User\Availability\Confirmation;
use Proximum\Vimeet\Application\Command\User\Availability\ConfirmationHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\Availability\ConfirmedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ConfirmationHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $dateTime = new \DateTime();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), Type::AVAILABILITY_CONFIRMATION, $user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $extraDataRepository
            ->add(new ExtraData($user->reveal(), $event->reveal(), Type::AVAILABILITY_CONFIRMATION, 'confirmed', $dateTime))
            ->shouldBeCalled()
        ;
        $eventDispatcher->dispatch(
            Events::USER_AVAILABILITY_CONFIRMED,
            new ConfirmedEvent($event->reveal(), $user->reveal())
        );

        $handler = new ConfirmationHandler(
            $eventDispatcher->reveal(),
            $extraDataRepository->reveal(),
            $dateTime
        );
        $handler->handle(new Confirmation($event->reveal(), $user->reveal()));
    }

    public function testHandleAlreadyCreated()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $extraData = $this->prophesize(ExtraData::class);
        $dateTime = new \DateTime();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), Type::AVAILABILITY_CONFIRMATION, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;
        $extraDataRepository->add(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $handler = new ConfirmationHandler(
            $eventDispatcher->reveal(),
            $extraDataRepository->reveal(),
            $dateTime
        );
        $handler->handle(new Confirmation($event->reveal(), $user->reveal()));
    }
}
