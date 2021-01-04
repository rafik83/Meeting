<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\Admin\SpotReassign;
use Proximum\Vimeet\Application\Command\Meeting\Admin\SpotReassignHandler;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotReassignHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        // Meeting1 is assigned to $meeting1Spot while sheet1's spot is available
        $meeting1 = $this->prophesize(Meeting::class);
        $meeting1->countParticipants()->willReturn(2);
        $meeting1->isBlockedSpot()->willReturn(false);
        $meeting1->isBlockedSlot()->willReturn(false);

        $meeting1Slot = $this->prophesize(MeetingSlot::class);
        $meeting1->getSlot()->willReturn($meeting1Slot);

        $meeting1Spot = $this->prophesize(Spot::class);
        $meeting1Spot->getId()->willReturn(42);
        $meeting1->getSpot()->willReturn($meeting1Spot);

        $meeting1FromSheet = $this->prophesize(Sheet::class);
        $meeting1->getFromSheet()->willReturn($meeting1FromSheet);
        $sheet1spot = $this->prophesize(Spot::class);
        $sheet1spot->getId()->willReturn(1337);
        $meeting1FromSheet->getSpot()->willReturn($sheet1spot);

        $meeting1ToSheet = $this->prophesize(Sheet::class);
        $meeting1->getToSheet()->willReturn($meeting1ToSheet);
        $meeting1ToSheet->getSpot()->willReturn(null);

        // Meeting2 is assigned to $meeting2Spot which is sheet1's spot
        $meeting2 = $this->prophesize(Meeting::class);
        $meeting2Spot = $this->prophesize(Spot::class);
        $meeting2->getSpot()->willReturn($meeting2Spot);

        $meeting2FromSheet = $this->prophesize(Sheet::class);
        $meeting2->getFromSheet()->willReturn($meeting2FromSheet);
        $meeting2FromSheet->getSpot()->willReturn($meeting2Spot);

        $meeting2ToSheet = $this->prophesize(Sheet::class);
        $meeting2->getToSheet()->willReturn($meeting2ToSheet);
        $meeting2ToSheet->getSpot()->willReturn(null);

        // Depencies mocks
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $visioGuesser = $this->prophesize(VisioGuesser::class);

        $spotReassignHandler = new SpotReassignHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $visioGuesser->reveal()
        );

        $meetingRepository
            ->getNonBlockedSpotByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$meeting1, $meeting2]);

        $visioGuesser->hasMeetingParticipantVisio($meeting1)->shouldBeCalled()->willReturn(false);

        $spotRepository
            ->getSpotsForSlotAndParticipantsQuantity(
                $meeting1Slot,
                2,
                $meeting1,
                null,
                null,
                false
            )
            ->shouldBeCalled()
            ->willReturn([$sheet1spot])
        ;

        $meeting1->updateSpot($sheet1spot, false, false)->shouldBeCalled();
        $meetingRepository->set($meeting1)->shouldBeCalled();
        $meetingRepository->set($meeting2)->shouldNotBeCalled();

        $spotReassignHandler->handle(new SpotReassign($event->reveal()));
    }
}
