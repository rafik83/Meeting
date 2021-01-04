<?php

namespace Proximum\Vimeet\Tests\Domain\Planner;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailabilityView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Planner\IndicatorView;
use Proximum\Vimeet\Domain\Planner\PlanningQuantityGuesser;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class IndicatorCalculatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $requestRepository;

    /** @var ObjectProphecy */
    private $slotRepository;

    /** @var ObjectProphecy */
    private $planningQuantityGuesser;

    /** @var ObjectProphecy */
    private $slotAvailability;

    /** @var IndicatorCalculator */
    private $indicatorCalculator;

    public function setUp()
    {
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->slotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $this->planningQuantityGuesser = $this->prophesize(PlanningQuantityGuesser::class);
        $this->slotAvailability = $this->prophesize(SlotAvailability::class);

        $this->indicatorCalculator = new IndicatorCalculator(
            $this->requestRepository->reveal(),
            $this->slotRepository->reveal(),
            $this->planningQuantityGuesser->reveal(),
            $this->slotAvailability->reveal()
        );
    }

    public function testGetIndicator()
    {
        $event = $this->prophesize(Event::class);

        $meetingSlot1 = $this->prophesize(MeetingSlot::class);
        $meetingSlot2 = $this->prophesize(MeetingSlot::class);
        $meetingSlot3 = $this->prophesize(MeetingSlot::class);
        $meetingSlot4 = $this->prophesize(MeetingSlot::class);
        $meetingSlot5 = $this->prophesize(MeetingSlot::class);

        $meetingSlots = [
            $meetingSlot1->reveal(),
            $meetingSlot2->reveal(),
            $meetingSlot3->reveal(),
            $meetingSlot4->reveal(),
            $meetingSlot5->reveal(),
        ];

        $this
            ->slotRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn($meetingSlots)
        ;

        $countBySheetParameters = [
            'state'           => Request::STATE_APPROVED,
            'disabled'        => false,
            'isFromAttending' => true,
            'isToAttending'   => true,
        ];

        /**
         * Sheet 1
         */
        $type = $this->prophesize(Type::class);
        $type->getNumberOfMeetingsPerPlanning()->shouldBeCalled()->willReturn(null);
        $type->getNumberMaxOfMeetingsPerSheet()->shouldBeCalled()->willReturn(null);
        $participantSheet1 = $this->prophesize(Participant::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet1->getEvent()->willReturn($event->reveal());
        $sheet1->countParticipants()->willReturn(1);
        $sheet1->getParticipantsArray()->willReturn([$participantSheet1->reveal()]);

        $this
            ->requestRepository
            ->countPendingPropositionReceivedBySheet($sheet1->reveal())
            ->shouldBeCalled()
            ->willReturn(5)
        ;

        $this
            ->planningQuantityGuesser
            ->guess($sheet1->reveal())
            ->shouldBeCalled()
            ->willReturn(6)
        ;

        $this
            ->requestRepository
            ->countSheetState($sheet1->reveal(), $countBySheetParameters)
            ->shouldBeCalled()
            ->willReturn(3)
        ;

        $this->slotAvailability->isUsable($sheet1->reveal(), $meetingSlot1->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet1->reveal(), $meetingSlot2->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet1->reveal(), $meetingSlot3->reveal())->shouldBeCalled()->willReturn(false);
        $this->slotAvailability->isUsable($sheet1->reveal(), $meetingSlot4->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet1->reveal(), $meetingSlot5->reveal())->shouldBeCalled()->willReturn(true);

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot1->reveal(), $participantSheet1)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot2->reveal(), $participantSheet1)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot3->reveal(), $participantSheet1)
            ->shouldNotBeCalled()
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot4->reveal(), $participantSheet1)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('meeting_unavailability'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot5->reveal(), $participantSheet1)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('mass_unavailability'))
        ;

        $this->assertEquals(
            new IndicatorView(4, 1, 1, 6, 3, 5, 1, null, null),
            $this->indicatorCalculator->getIndicator($sheet1->reveal())
        );

        /**
         * Sheet 2
         */
        $type2 = $this->prophesize(Type::class);
        $type2->getNumberOfMeetingsPerPlanning()->shouldBeCalled()->willReturn(null);
        $type2->getNumberMaxOfMeetingsPerSheet()->shouldBeCalled()->willReturn(null);
        $participant1Sheet2 = $this->prophesize(Participant::class);
        $participant2Sheet2 = $this->prophesize(Participant::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->countParticipants()->willReturn(2);
        $sheet2->getParticipantsArray()->willReturn([$participant1Sheet2->reveal(), $participant2Sheet2->reveal()]);
        $sheet2->getType()->shouldBeCalled()->willReturn($type2->reveal());

        $this
            ->requestRepository
            ->countPendingPropositionReceivedBySheet($sheet2->reveal())
            ->shouldBeCalled()
            ->willReturn(2)
        ;

        $this
            ->planningQuantityGuesser
            ->guess($sheet2->reveal())
            ->shouldBeCalled()
            ->willReturn(8)
        ;

        $this
            ->requestRepository
            ->countSheetState($sheet2->reveal(), $countBySheetParameters)
            ->shouldBeCalled()
            ->willReturn(1)
        ;

        $this->slotAvailability->isUsable($sheet2->reveal(), $meetingSlot1->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet2->reveal(), $meetingSlot2->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet2->reveal(), $meetingSlot3->reveal())->shouldBeCalled()->willReturn(false);
        $this->slotAvailability->isUsable($sheet2->reveal(), $meetingSlot4->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet2->reveal(), $meetingSlot5->reveal())->shouldBeCalled()->willReturn(false);

        // participant 1 of Sheet 2
        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot1->reveal(), $participant1Sheet2)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('meeting_unavailability'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot2->reveal(), $participant1Sheet2)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot3->reveal(), $participant1Sheet2)
            ->shouldNotBeCalled()
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot4->reveal(), $participant1Sheet2)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot5->reveal(), $participant1Sheet2)
            ->shouldNotBeCalled()
        ;

        // participant 2 of Sheet 2
        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot1->reveal(), $participant2Sheet2)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot2->reveal(), $participant2Sheet2)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot3->reveal(), $participant2Sheet2)
            ->shouldNotBeCalled()
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot4->reveal(), $participant2Sheet2)
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot5->reveal(), $participant2Sheet2)
            ->shouldNotBeCalled()
        ;

        $this->assertEquals(
            new IndicatorView(3, 2, 0, 8, 1, 2, 0, null, null),
            $this->indicatorCalculator->getIndicator($sheet2->reveal())
        );

        /**
         * Sheet 3 with numberOfMeetingsPerPlanning
         */
        $type3 = $this->prophesize(Type::class);
        $type3->getNumberOfMeetingsPerPlanning()->shouldBeCalled()->willReturn(10);
        $type3->getNumberMaxOfMeetingsPerSheet()->shouldBeCalled()->willReturn(null);
        $participant1Sheet3 = $this->prophesize(Participant::class);
        $participant2Sheet3 = $this->prophesize(Participant::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->countParticipants()->willReturn(2);
        $sheet3->getParticipantsArray()->willReturn([$participant1Sheet3->reveal(), $participant2Sheet3->reveal()]);
        $sheet3->getType()->shouldBeCalled()->willReturn($type3->reveal());

        $this
            ->requestRepository
            ->countPendingPropositionReceivedBySheet($sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(2)
        ;

        $this
            ->planningQuantityGuesser
            ->guess($sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(2)
        ;

        $this
            ->requestRepository
            ->countSheetState($sheet3->reveal(), $countBySheetParameters)
            ->shouldBeCalled()
            ->willReturn(1)
        ;

        $this->slotAvailability->isUsable($sheet3->reveal(), $meetingSlot1->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet3->reveal(), $meetingSlot2->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet3->reveal(), $meetingSlot3->reveal())->shouldBeCalled()->willReturn(false);
        $this->slotAvailability->isUsable($sheet3->reveal(), $meetingSlot4->reveal())->shouldBeCalled()->willReturn(true);
        $this->slotAvailability->isUsable($sheet3->reveal(), $meetingSlot5->reveal())->shouldBeCalled()->willReturn(false);

        // participant 1 of Sheet 3
        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot1->reveal(), $participant1Sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('meeting_unavailability'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot2->reveal(), $participant1Sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot3->reveal(), $participant1Sheet3->reveal())
            ->shouldNotBeCalled()
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot4->reveal(), $participant1Sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot5->reveal(), $participant1Sheet3->reveal())
            ->shouldNotBeCalled()
        ;

        // participant 2 of Sheet 3
        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot1->reveal(), $participant2Sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot2->reveal(), $participant2Sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot3->reveal(), $participant2Sheet3->reveal())
            ->shouldNotBeCalled()
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot4->reveal(), $participant2Sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(new SlotAvailabilityView('slot_available'))
        ;

        $this
            ->slotAvailability
            ->getSlotAvailability($meetingSlot5->reveal(), $participant2Sheet3->reveal())
            ->shouldNotBeCalled()
        ;

        $this->assertEquals(
            new IndicatorView(3, 2, 0, 2, 1, 2, 0, 10, null),
            $this->indicatorCalculator->getIndicator($sheet3->reveal())
        );
    }
}
