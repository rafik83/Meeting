<?php

namespace Proximum\Vimeet\Tests\Application\Command\Group;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Group\UserAvailabilitiesBuilderCache;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\SlotView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UserAvailabilitiesBuilderCacheTest extends TestCase
{
    public function testBuildAvailabilitiesByUserAndEventFromSkeleton()
    {
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(1);

        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $slot          = new MeetingSlot($event, $begin, $end);
        $slotView      = new SlotView($slot);
        $slotView->available = false;
        $agendaDayView = new AgendaDayView([$slotView]);

        // user slot
        $meetingSlot = new MeetingSlot($event, $begin, $end);

        $participant = $this->prophesize(Participant::class);
        $participant->getId()->willReturn(10);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);

        $participantRepository->getParticipantsByUserForEvent(1, $event)
            ->shouldBeCalled()
            ->willReturn([$participant->reveal()]);

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants(
                $event,
                [$participant->reveal()],
                true
            )
            ->shouldBeCalled()
            ->willReturn([$meetingSlot])
        ;

        // Expected
        $expectedSlot = new MeetingSlot($event, $begin, $end);
        $expectedSlotView = new SlotView($expectedSlot);
        $expectedSlotView->available = true; // expected to be available
        $expectedAgendaDayView = new AgendaDayView([$expectedSlotView]);

        $userAvailabilitiesBuilderCache = new UserAvailabilitiesBuilderCache(
            $participantRepository->reveal(),
            $meetingSlotRepository->reveal()
        );

        $agendaDayViewBuilded = $userAvailabilitiesBuilderCache->buildAvailabilitiesByUserAndEventFromSkeleton(
            $user->reveal(),
            $event,
            [$agendaDayView]
        );

        $this->assertEquals([$expectedAgendaDayView], $agendaDayViewBuilded);
    }
}
