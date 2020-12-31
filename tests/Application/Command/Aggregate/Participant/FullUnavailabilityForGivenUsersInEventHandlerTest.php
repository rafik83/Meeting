<?php

namespace Proximum\Vimeet\Tests\Application\Command\Aggregate\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenUsersInEvent;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenUsersInEventHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityForGivenUsersInEventHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $participantUnavailableAggregator = $this->prophesize(ParticipantUnavailableAggregator::class);

        $userRepository->getByIdsIndexedById([1, 2, 3])->shouldBeCalled()->willReturn([$user1, $user2, $user3]);

        $participantUnavailableAggregator
            ->aggregateUnavailability($user1->reveal(), $event->reveal())
            ->shouldBeCalled();

        $participantUnavailableAggregator
            ->aggregateUnavailability($user2->reveal(), $event->reveal())
            ->shouldBeCalled();

        $participantUnavailableAggregator
            ->aggregateUnavailability($user3->reveal(), $event->reveal())
            ->shouldBeCalled();

        $handler = new FullUnavailabilityForGivenUsersInEventHandler(
            $userRepository->reveal(),
            $participantUnavailableAggregator->reveal()
        );

        $handler->handle(new FullUnavailabilityForGivenUsersInEvent($event->reveal(), [1, 2, 3]));
    }
}
