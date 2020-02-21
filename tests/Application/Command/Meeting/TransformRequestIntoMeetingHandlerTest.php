<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Slot\SlotFilter;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;

class TransformRequestIntoMeetingHandlerTest extends TestCase
{
    /** @var ObjectProphecy|AvailableSpots */
    private $availableSpots;

    /** @var ObjectProphecy|MeetingParticipants */
    private $meetingParticipants;

    /** @var ObjectProphecy|MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var ObjectProphecy|MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var ObjectProphecy|SlotFilter */
    private $slotFilter;

    /** @var ObjectProphecy|VisioGuesser */
    private $visioGuesser;

    /** @var \DateTime */
    private $dateTime;

    /** @var TransformRequestIntoMeetingHandler */
    private $transformRequestIntoMeetingHandler;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Sheet */
    private $fromSheet;

    /** @var ObjectProphecy|Sheet */
    private $toSheet;

    /** @var ObjectProphecy|User */
    private $creator;

    public function setUp()
    {
        $this->creator = $this->prophesize(User::class);
        $this->fromSheet = $this->prophesize(Sheet::class);
        $this->toSheet = $this->prophesize(Sheet::class);
        $this->event = $this->prophesize(Event::class);

        $this->availableSpots = $this->prophesize(AvailableSpots::class);
        $this->meetingParticipants = $this->prophesize(MeetingParticipants::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $this->slotFilter = $this->prophesize(SlotFilter::class);
        $this->visioGuesser = $this->prophesize(VisioGuesser::class);
        $this->dateTime = new \DateTime();

        $this->transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $this->availableSpots->reveal(),
            $this->meetingParticipants->reveal(),
            $this->meetingRepository->reveal(),
            $this->meetingSlotRepository->reveal(),
            $this->slotFilter->reveal(),
            $this->visioGuesser->reveal(),
            $this->dateTime
        );
    }

    public function testNoPreferenceAndNoMatch(): void
    {
        $fromParticipant = $this->prophesize(Participant::class);
        $toParticipant = $this->prophesize(Participant::class);

        $this->toSheet->getParticipantsArray()->willReturn([$toParticipant->reveal()]);

        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);

        $request = new Request(
            $this->fromSheet->reveal(),
            [$fromParticipant->reveal()],
            $this->toSheet->reveal(),
            [],
            $this->dateTime,
            $this->creator->reveal(),
            $this->event->reveal()
        );

        $this->meetingParticipants
            ->getMeetingParticipants($request, $this->fromSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$fromParticipant->reveal()])
        ;
        $this->meetingParticipants
            ->getMeetingParticipants($request, $this->toSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->meetingSlotRepository
            ->findAvailableSlotsByParticipants(
                $this->event->reveal(),
                [$fromParticipant->reveal(), $toParticipant->reveal()]
            )
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal(), $slot2->reveal()])
        ;

        $this->slotFilter
            ->getFilteredSlots([$slot1->reveal(), $slot2->reveal()])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->assertNull(
            $this->transformRequestIntoMeetingHandler->handle(
                new TransformRequestIntoMeeting($request, Meeting::CREATED_BY_PLANNER)
            )
        );
    }

    public function testAddMeeting()
    {
        $fromParticipant = $this->prophesize(Participant::class);
        $toParticipant = $this->prophesize(Participant::class);

        $request = new Request(
            $this->fromSheet->reveal(),
            [],
            $this->toSheet->reveal(),
            [],
            $this->dateTime,
            $this->creator->reveal(),
            $this->event->reveal()
        );

        $this->meetingParticipants
            ->getMeetingParticipants($request, $this->fromSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$fromParticipant->reveal()])
        ;
        $this->meetingParticipants
            ->getMeetingParticipants($request, $this->toSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$toParticipant->reveal()])
        ;

        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot3 = $this->prophesize(MeetingSlot::class);

        $this->meetingSlotRepository
            ->findAvailableSlotsByParticipants(
                $this->event->reveal(),
                [$fromParticipant->reveal(), $toParticipant->reveal()]
            )
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal(), $slot2->reveal()])
        ;

        $this->slotFilter
            ->getFilteredSlots([$slot1->reveal(), $slot2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot2->reveal(), $slot3->reveal()])
        ;

        $this->visioGuesser
            ->isParticipantVisio([$fromParticipant->reveal(), $toParticipant->reveal()])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->availableSpots
            ->getBySlot($slot2->reveal(), $this->fromSheet->reveal(), $this->toSheet->reveal(), 2, false)
            ->shouldBeCalled()
            ->willThrow(NoSpotsAvailableForThisSlotAndMeetingException::class)
        ;

        $spot = $this->prophesize(Spot::class);

        $this->availableSpots
            ->getBySlot($slot3->reveal(), $this->fromSheet->reveal(), $this->toSheet->reveal(), 2, false)
            ->shouldBeCalled()
            ->willReturn($spot->reveal())
        ;

        $expectedMeeting = new Meeting(
            $request,
            $slot3->reveal(),
            $this->fromSheet->reveal(),
            [$fromParticipant->reveal()],
            $this->toSheet->reveal(),
            [$toParticipant->reveal()],
            $this->dateTime,
            $spot->reveal(),
            $this->event->reveal(),
            false,
            true,
            Meeting::CREATED_BY_PLANNER
        );

        $this->meetingRepository
            ->add($expectedMeeting)
            ->shouldBeCalled()
        ;

        $this->assertEquals(
            $expectedMeeting,
            $this->transformRequestIntoMeetingHandler->handle(
                new TransformRequestIntoMeeting($request, Meeting::CREATED_BY_PLANNER, false, true)
            )
        );
    }
}
