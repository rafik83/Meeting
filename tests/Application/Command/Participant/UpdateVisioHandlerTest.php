<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Participant\UpdateVisio;
use Proximum\Vimeet\Application\Command\Participant\UpdateVisioHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantVisioToggledEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

class UpdateVisioHandlerTest extends TestCase
{
    public function testHandleUpdatedVisioToTrue(): void
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();

        $participant = $this->prophesize(Participant::class);
        $participant->getEvent()->willReturn($event);
        $participant->getUser()->willReturn($user);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->getExtraDataForEventNameAndUser($event, Type::IS_PARTICIPANT_VISIO, $user)
            ->shouldBeCalled()
            ->willReturn(null);

        $extraDataRepository->add(
            new ExtraData(
                $user,
                $event,
                Type::IS_PARTICIPANT_VISIO,
                true,
                $now
            )
        )
            ->shouldBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher
            ->dispatch(
                Events::PARTICIPANT_VISIO_TOGGLED,
                new ParticipantVisioToggledEvent($participant->reveal(), true)
            )
            ->shouldBeCalled()
        ;

        $command = new UpdateVisio($participant->reveal(), true);
        $handler = new UpdateVisioHandler($extraDataRepository->reveal(), $delayedEventDispatcher->reveal(), $now);

        $handler->handle($command);
    }

    public function testHandleUpdatedVisioToFalse(): void
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();

        $extraData = $this->prophesize(ExtraData::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getEvent()->willReturn($event);
        $participant->getUser()->willReturn($user);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->getExtraDataForEventNameAndUser($event, Type::IS_PARTICIPANT_VISIO, $user)
            ->shouldBeCalled()
            ->willReturn($extraData->reveal());

        $extraDataRepository->remove($extraData->reveal())
            ->shouldBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher
            ->dispatch(
                Events::PARTICIPANT_VISIO_TOGGLED,
                new ParticipantVisioToggledEvent($participant->reveal(), false)
            )
            ->shouldBeCalled()
        ;

        $command = new UpdateVisio($participant->reveal(), false);
        $handler = new UpdateVisioHandler($extraDataRepository->reveal(), $delayedEventDispatcher->reveal(), $now);

        $handler->handle($command);
    }
}
