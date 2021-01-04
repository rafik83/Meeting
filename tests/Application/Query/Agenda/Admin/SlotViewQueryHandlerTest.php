<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Slot\MassUnavailabilitySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MeetingOnOtherSheetView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MeetingSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\UnavailabilitySlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailabilityView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SlotViewQueryHandlerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var \DateTimeInterface */
    private $start;

    /** @var \DateTimeInterface */
    private $end;

    /** @var Day */
    private $day;

    /** @var User */
    private $user;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var string */
    private $locale;

    /** @var array */
    private $happenings;

    /** @var array */
    private $unavailabilities;

    /** @var array */
    private $masses;

    /** @var Meeting[] */
    private $meetings;

    /** @var array */
    private $massAssignments;

    /** @var Meeting[] */
    private $meetingOtherSheets;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var SlotAvailability */
    private $slotAvailability;

    /** @var MeetingSlot */
    private $slot;

    public function setUp()
    {
        $this->event       = EventFactory::createEvent();
        $this->start       = new \DateTime();
        $this->end         = new \DateTime();
        $this->day         = new Day($this->event, $this->start, $this->end);
        $this->locale      = 'fr';
        $this->user        = new User('john@doh.com', 'salt', 'password', $this->locale);
        $this->sheet       = SheetFactory::create($this->event, $this->user);
        $this->participant = ParticipantFactory::create($this->sheet, $this->user);
        $this->slot        = new MeetingSlot($this->event, new \DateTime(), new \DateTime(), false);

        $this->happenings         = [];
        $this->unavailabilities   = [];
        $this->masses             = [];
        $this->meetings           = [];
        $this->massAssignments    = [];
        $this->meetingOtherSheets = [];

        $this->meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $this->slotAvailability      = $this->prophesize(SlotAvailability::class);
        $this->sheetInfoGuesser      = $this->prophesize(SheetInfoGuesser::class);
    }

    public function testHandle()
    {
        $slotAvailabilityView = new SlotAvailabilityView(SlotAvailability::UNAVAILABILITY);

        $this->meetingSlotRepository->findByEventAndDay($this->event, $this->day)->shouldBeCalled()->willReturn([$this->slot]);

        $this->preloadMethodShouldBeCalled();

        $this->slotAvailability->getSlotAvailability($this->slot, $this->participant)->shouldBeCalled()->willReturn($slotAvailabilityView);
        $this->sheetInfoGuesser->guessSheetTitle($this->sheet, $this->locale)->shouldNotBeCalled();

        $handler = new SlotViewQueryHandler(
            $this->meetingSlotRepository->reveal(),
            $this->slotAvailability->reveal(),
            $this->sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new SlotViewQuery(
            $this->event,
            $this->day,
            $this->sheet,
            $this->participant,
            $this->happenings,
            $this->unavailabilities,
            $this->masses,
            $this->meetings,
            $this->massAssignments,
            $this->meetingOtherSheets
        ));

        $expected = [new UnavailabilitySlotView(
            $this->slot,
            SlotAvailability::UNAVAILABILITY
        )];

        $this->assertEquals($expected, $result);
    }

    public function testSheetNotAttendHandle()
    {
        $this->sheet->setAttendance(false);

        $this->meetingSlotRepository->findByEventAndDay($this->event, $this->day)->shouldBeCalled()->willReturn([$this->slot]);

        $this->preloadMethodShouldBeCalled();

        $this->slotAvailability->getSlotAvailability($this->slot, $this->participant)->shouldNotBeCalled();
        $this->sheetInfoGuesser->guessSheetTitle($this->sheet, $this->locale)->shouldNotBeCalled();

        $handler = new SlotViewQueryHandler(
            $this->meetingSlotRepository->reveal(),
            $this->slotAvailability->reveal(),
            $this->sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(
            new SlotViewQuery(
                $this->event,
                $this->day,
                $this->sheet,
                $this->participant,
                $this->happenings,
                $this->unavailabilities,
                $this->masses,
                $this->meetings,
                $this->massAssignments,
                $this->meetingOtherSheets
            )
        );

        $expected = [
            new UnavailabilitySlotView(
                $this->slot,
                SlotAvailability::UNAVAILABILITY
            ),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testLockedSlotHandle()
    {
        $this->slot->lock();

        $slotAvailabilityView = new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE);

        $this->meetingSlotRepository->findByEventAndDay($this->event, $this->day)->shouldBeCalled()->willReturn([$this->slot]);

        $this->preloadMethodShouldBeCalled();

        $this->slotAvailability->getSlotAvailability($this->slot, $this->participant)->shouldBeCalled()->willReturn($slotAvailabilityView);
        $this->sheetInfoGuesser->guessSheetTitle($this->sheet, $this->locale)->shouldNotBeCalled();

        $handler = new SlotViewQueryHandler(
            $this->meetingSlotRepository->reveal(),
            $this->slotAvailability->reveal(),
            $this->sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(
            new SlotViewQuery(
                $this->event,
                $this->day,
                $this->sheet,
                $this->participant,
                $this->happenings,
                $this->unavailabilities,
                $this->masses,
                $this->meetings,
                $this->massAssignments,
                $this->meetingOtherSheets
            )
        );

        $expected = [
            new MassUnavailabilitySlotView(
                $this->slot,
                SlotAvailability::MASS_UNAVAILABILITY
            ),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithMeeting()
    {
        $start        = new \DateTime('2016-10-12 10:00:00.000');
        $end          = new \DateTime('2016-10-12 18:00:00.000');
        $day          = new Day($this->event, $start, $end);
        $user2        = new User('john@doh.com2', 'salt2', 'password2', $this->locale);
        $sheet2       = SheetFactory::create($this->event, $user2);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->sheet, 1);
        $property->setValue($sheet2, 2);
        $property->setAccessible(false);

        $participant2 = ParticipantFactory::create($sheet2, $user2);
        $slot         = new MeetingSlot($this->event, new \DateTime('2016-10-12 11:00:00.000'), new \DateTime('2016-10-12 12:00:00.000'), false);

        $spot           = new Spot('ref', $this->event, 2, 3, 4, true);
        $reflectionSpot = new \ReflectionClass(Spot::class);
        $propertySpot   = $reflectionSpot->getProperty('id');
        $propertySpot->setAccessible(true);
        $propertySpot->setValue($spot, 10);
        $propertySpot->setAccessible(false);

        $request = new Request($this->sheet, [], $sheet2, [$participant2], new \DateTime(), $this->user, $this->event);
        $meeting = new Meeting(
            $request,
            $slot,
            $this->sheet,
            [$this->participant],
            $sheet2,
            [$participant2],
            new \DateTime(),
            $spot,
            $this->event
        );

        $reflectionM  = new \ReflectionClass(Meeting::class);
        $propertyM = $reflectionM->getProperty('id');
        $propertyM->setAccessible(true);
        $propertyM->setValue($meeting, 1);
        $propertyM->setAccessible(false);

        $slotAvailabilityView = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        $this->meetingSlotRepository->findByEventAndDay($this->event, $day)->shouldBeCalled()->willReturn([$slot]);

        $this->preloadMethodShouldBeCalled();

        $this->slotAvailability->getSlotAvailability($slot, $this->participant)->shouldBeCalled()->willReturn($slotAvailabilityView);
        $this->sheetInfoGuesser->guessSheetTitle($sheet2)->shouldBeCalled()->willReturn('sheetMetTitle');

        $handler = new SlotViewQueryHandler(
            $this->meetingSlotRepository->reveal(),
            $this->slotAvailability->reveal(),
            $this->sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new SlotViewQuery(
            $this->event,
            $day,
            $this->sheet,
            $this->participant,
            $this->happenings,
            $this->unavailabilities,
            $this->masses,
            $this->meetings,
            $this->massAssignments,
            $this->meetingOtherSheets
        ));

        $expected = [new MeetingSlotView(
            $slot,
            SlotAvailability::MEETING_UNAVAILABILITY,
            10,
            'ref',
            2,
            'sheetMetTitle',
            1,
            true,
            false,
            false
        )];

        $this->assertEquals($expected, $result);
    }

    public function testMeetingOnOthersheet()
    {
        $slotAvailabilityView = new SlotAvailabilityView(
            SlotAvailability::MEETING_ON_OTHER_SHEET,
            null,
            null,
            $this->sheet
        );

        $this->meetingSlotRepository->findByEventAndDay($this->event, $this->day)->shouldBeCalled()->willReturn([$this->slot]);

        $this->preloadMethodShouldBeCalled();

        $this->slotAvailability->getSlotAvailability($this->slot, $this->participant)->shouldBeCalled()->willReturn($slotAvailabilityView);
        $this->sheetInfoGuesser->guessSheetTitle($this->sheet)->shouldBeCalled()->willReturn('otherSheetTitle');

        $handler = new SlotViewQueryHandler(
            $this->meetingSlotRepository->reveal(),
            $this->slotAvailability->reveal(),
            $this->sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new SlotViewQuery(
            $this->event,
            $this->day,
            $this->sheet,
            $this->participant,
            $this->happenings,
            $this->unavailabilities,
            $this->masses,
            $this->meetings,
            $this->massAssignments,
            $this->meetingOtherSheets
        ));

        $expected = [new MeetingOnOtherSheetView(
            $this->slot,
            SlotAvailability::MEETING_ON_OTHER_SHEET,
            'otherSheetTitle',
            ''
        )];

        $this->assertEquals($expected, $result);
    }

    private function preloadMethodShouldBeCalled()
    {
        return $this
            ->slotAvailability
            ->preload(
                $this->happenings,
                $this->meetings,
                $this->unavailabilities,
                $this->masses,
                $this->massAssignments,
                $this->meetingOtherSheets
            )
            ->shouldBeCalled();
    }
}
