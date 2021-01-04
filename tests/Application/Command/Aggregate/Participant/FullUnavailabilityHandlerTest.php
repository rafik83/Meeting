<?php

namespace Proximum\Vimeet\Tests\Application\Command\Aggregate\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailability;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityHandlerTest extends TestCase
{
    public function testHandleOnlyCatalog()
    {
        $event = $this->prophesize(Event::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);

        $userRepository
            ->findByEventAndInCatalog($event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $user1->reveal(),
                    $user2->reveal(),
                    $user3->reveal(),
                ]
            )
        ;

        $userRepository
            ->findWithEnabledSheetByEvent($event->reveal())
            ->shouldNotBeCalled();

        $participantFullUnavailabilityAggregator = $this->prophesize(ParticipantUnavailableAggregator::class);

        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($user1->reveal(), $event->reveal())
            ->shouldBeCalled();

        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($user2->reveal(), $event->reveal())
            ->shouldBeCalled();

        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($user3->reveal(), $event->reveal())
            ->shouldBeCalled();

        $handler = new FullUnavailabilityHandler(
            $userRepository->reveal(),
            $participantFullUnavailabilityAggregator->reveal()
        );

        $handler->handle(new FullUnavailability($event->reveal(), true));
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);

        $userRepository
            ->findWithEnabledSheetByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $user1->reveal(),
                    $user2->reveal(),
                    $user3->reveal(),
                ]
            )
        ;

        $userRepository
            ->findByEventAndInCatalog($event->reveal())
            ->shouldNotBeCalled();

        $participantFullUnavailabilityAggregator = $this->prophesize(ParticipantUnavailableAggregator::class);

        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($user1->reveal(), $event->reveal())
            ->shouldBeCalled();

        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($user2->reveal(), $event->reveal())
            ->shouldBeCalled();

        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($user3->reveal(), $event->reveal())
            ->shouldBeCalled();

        $handler = new FullUnavailabilityHandler(
            $userRepository->reveal(),
            $participantFullUnavailabilityAggregator->reveal()
        );

        $handler->handle(new FullUnavailability($event->reveal(), false));
    }
}
