<?php

namespace Proximum\Vimeet\Tests\Application\Query\MeetingSlot;

use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQuery;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class GetAvailableSlotsQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $event = $this->prophesize(Event::class);
        $meeting = $this->prophesize(Meeting::class);
        $meeting->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $meeting->getAllParticipants()->shouldBeCalled()->willReturn([$participant->reveal()]);
        $meeting->countParticipants()->shouldBeCalled()->willReturn(1);
        $meeting->getFromSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $meeting->getToSheet()->shouldBeCalled()->willReturn($sheet2->reveal());
        $meetingSlot1 = $this->prophesize(MeetingSlot::class);
        $meetingSlot2 = $this->prophesize(MeetingSlot::class);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findAvailableSlotsByParticipants($event->reveal(), [$participant->reveal()], false)
            ->shouldBeCalled()
            ->willReturn([$meetingSlot1->reveal(), $meetingSlot2->reveal()]);

        $spotRepository
            ->hasSpotsForSlotAndParticipantsQuantity($meetingSlot1->reveal(), 1, $meeting->reveal(), $sheet1->reveal(), $sheet2->reveal(), false)
            ->shouldBeCalled()
            ->willReturn(true);

        $spotRepository
            ->hasSpotsForSlotAndParticipantsQuantity($meetingSlot2->reveal(), 1, $meeting->reveal(), $sheet1->reveal(), $sheet2->reveal(), false)
            ->shouldBeCalled()
            ->willReturn(false);

        $handler = new GetAvailableSlotsQueryHandler($spotRepository->reveal(), $meetingSlotRepository->reveal());
        $result = $handler->handle(new GetAvailableSlotsQuery($meeting->reveal(), false));
        $expectedResult = new GetAvailableSlotsView([
            $meetingSlot1->reveal()
        ]);

        $this->assertEquals($result, $expectedResult);
    }
}
