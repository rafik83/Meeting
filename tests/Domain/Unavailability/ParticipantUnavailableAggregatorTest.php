<?php

namespace Proximum\Vimeet\Tests\Domain\Unavailability;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class ParticipantUnavailableAggregatorTest extends TestCase
{
    public function testAggregateWithNoSlot()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getId()->willReturn(123);
        $sheet->getEvent()->willReturn($event->reveal());

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findAvailableSlotsByParticipants($event->reveal(), [$participant->reveal()])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->getAllParticipantForUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant])
        ;
        $participantRepository->set($participant->reveal())->shouldBeCalled();

        $participant->setFullyUnavailable(true)->shouldBeCalled();

        $participantUnavailableAggregator = new ParticipantUnavailableAggregator(
            $meetingSlotRepository->reveal(),
            $participantRepository->reveal()
        );
        $participantUnavailableAggregator->aggregateUnavailability($user->reveal(), $event->reveal());
    }

    public function testAggregateWithSlots()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $slot  = $this->prophesize(MeetingSlot::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getId()->willReturn(123);
        $sheet->getEvent()->willReturn($event->reveal());

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findAvailableSlotsByParticipants($event->reveal(), [$participant->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot->reveal()])
        ;

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->getAllParticipantForUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant])
        ;
        $participantRepository->set($participant->reveal())->shouldBeCalled();

        $participant->setFullyUnavailable(false)->shouldBeCalled();

        $participantUnavailableAggregator = new ParticipantUnavailableAggregator(
            $meetingSlotRepository->reveal(),
            $participantRepository->reveal()
        );
        $participantUnavailableAggregator->aggregateUnavailability($user->reveal(), $event->reveal());
    }
}
