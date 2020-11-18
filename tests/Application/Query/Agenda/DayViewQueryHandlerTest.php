<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Agenda\AgendaCollisionManager;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\CancelAttendanceUnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\CancelAttendanceUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\DayViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\DayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\HappeningViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\HappeningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MassUnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\MassUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\CancelAttendanceUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingOwnSheetParticipantView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\SheetMetView;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class DayViewQueryHandlerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var Category */
    private $category;

    /** @var User */
    private $user;

    /** @var TimeRangeView */
    private $day;

    /** @var \DateTimeInterface */
    private $startTime;

    /** @var \DateTimeInterface */
    private $endTime;

    /** @var \DateTimeInterface */
    private $currentTime;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var HappeningView[] */
    private $expectedHappeningViews;

    /** @var MassUnavailabilityView */
    private $massView;

    /** @var \DateTimeInterface */
    private $beginHappening1;

    /** @var \DateTimeInterface */
    private $beginHappening2;

    /** @var \DateTimeInterface */
    private $endHappening1;

    /** @var \DateTimeInterface */
    private $endHappening2;

    /** @var Unavailability\Category */
    private $massCategory;

    /** @var Happening\Category */
    private $categoryH1;

    /** @var Happening\Category */
    private $categoryH2;

    /** @var Happening */
    private $happening1;

    /** @var Happening */
    private $happening2;

    /** @var HappeningParticipation */
    private $participation1;

    /** @var HappeningParticipation */
    private $participation2;

    /** @var Unavailability */
    private $unavailability;

    /** @var Unavailability\Mass */
    private $mass;

    /** @var UnavailabilityView */
    private $unavailabilityView;

    /** @var DayView */
    private $expectedDayView;

    /** @var AgendaCollisionManager */
    private $agendaCollisionManager;

    /** @var AvailableSlotsByParticipantAndDayQueryHandler */
    private $availableSlotsByParticipantAndDayQueryHandler;

    /** @var ObjectProphecy */
    private $cancelAttendanceUnavailabilityViewQueryHandler;

    /** @var ObjectProphecy */
    private $happeningViewQueryHandler;

    /** @var ObjectProphecy */
    private $massHandler;

    /** @var ObjectProphecy */
    private $unavailabilityHandler;

    /** @var ObjectProphecy */
    private $meetingHandler;

    /** @var OverlappedTimeRangeMerger */
    private $overlappedTimeRangeMerger;

    /** @var DayViewQueryHandler */
    private $dayViewQueryHandler;

    public function setUp(): void
    {
        $this->currentTime = new \DateTime('2016-10-12 15:00:00');
        $this->event = EventFactory::createEvent();
        $this->category = null;
        $this->user = UserFactory::create();
        $this->startTime = new \DateTime('2016-10-12 10:00:00');
        $this->endTime = new \DateTime('2016-10-12 18:00:00');
        $this->day = new TimeRangeView($this->startTime, $this->endTime);
        $this->sheet = SheetFactory::create($this->event);
        $this->sheet->setInCatalog(true);
        $this->participant = ParticipantFactory::create($this->sheet, $this->user);

        $this->beginHappening1 = new \DateTime('2016-10-12 12:00:00');
        $this->beginHappening2 = new \DateTime('2016-10-12 15:30:00');
        $this->endHappening1   = new \DateTime('2016-10-12 14:00:00');
        $this->endHappening2   = new \DateTime('2016-10-12 16:50:00');

        $this->expectedHappeningViews = [
            new HappeningView(1, $this->beginHappening1, $this->endHappening1, 'title', 'description', [], 'picto', 'leftColor', 'rightColor', 'Europe/Paris'),
            new HappeningView(2, $this->beginHappening2, $this->endHappening2, 'title2', 'description2', [], 'picto', 'leftColor', 'rightColor', 'Europe/Paris'),
        ];

        $this->massView = new MassUnavailabilityView(1, $this->beginHappening1, $this->endHappening1, 'title', 'description', 'picto', 'leftColor', 'rightColor', 'Europe/Paris', false);
        $this->massCategory = new Unavailability\Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $this->categoryH1 = new Happening\Category($this->event, 'Conference', 1, '#123123', '#123123');
        $this->categoryH2 = new Happening\Category($this->event, 'RDV', 2, '#123123', '#123123');
        $this->happening1 = new Happening($this->event, $this->beginHappening1, $this->endHappening1, $this->categoryH1, []);
        $this->happening2 = new Happening($this->event, $this->beginHappening2, $this->endHappening2, $this->categoryH2, []);
        $this->participation1 = new HappeningParticipation($this->happening1, $this->user);
        $this->participation2 = new HappeningParticipation($this->happening2, $this->user);
        $this->unavailability = new Unavailability($this->participant->getUser(), $this->event, $this->beginHappening2, $this->endHappening2);
        $this->mass           = new Unavailability\Mass($this->event, $this->massCategory, 'name', $this->beginHappening1, $this->endHappening1, true);
        $this->unavailabilityView = new UnavailabilityView(
            1,
            $this->beginHappening2,
            $this->endHappening2,
            'Europe/Paris',
            null,
            true,
            true
        );
        $this->expectedDayView = new DayView(
            $this->startTime,
            $this->endTime,
            $this->event->getConfiguration()->getScheduleScale(),
            [$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]],
            [$this->unavailabilityView],
            [$this->massView],
            [],
            [],
            []
        );

        $this->agendaCollisionManager = $this->prophesize(AgendaCollisionManager::class);
        $this->availableSlotsByParticipantAndDayQueryHandler = $this->prophesize(
            AvailableSlotsByParticipantAndDayQueryHandler::class
        );
        $this->cancelAttendanceUnavailabilityViewQueryHandler = $this->prophesize(
            CancelAttendanceUnavailabilityViewQueryHandler::class
        );
        $this->happeningViewQueryHandler = $this->prophesize(HappeningViewQueryHandler::class);
        $this->massHandler = $this->prophesize(MassUnavailabilityViewQueryHandler::class);
        $this->unavailabilityHandler = $this->prophesize(UnavailabilityViewQueryHandler::class);
        $this->meetingHandler = $this->prophesize(MeetingViewQueryHandler::class);
        $this->overlappedTimeRangeMerger = new OverlappedTimeRangeMerger();

        $this->dayViewQueryHandler = new DayViewQueryHandler(
            $this->happeningViewQueryHandler->reveal(),
            $this->unavailabilityHandler->reveal(),
            $this->massHandler->reveal(),
            $this->meetingHandler->reveal(),
            $this->cancelAttendanceUnavailabilityViewQueryHandler->reveal(),
            $this->availableSlotsByParticipantAndDayQueryHandler->reveal(),
            $this->agendaCollisionManager->reveal(),
            $this->overlappedTimeRangeMerger
        );
    }

    public function testHandle(): void
    {
        $this->happeningViewQueryHandler
            ->handle(
                new HappeningViewQuery(
                    $this->user,
                    $this->happening1,
                    $this->event,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn($this->expectedHappeningViews[0]);

        $this->happeningViewQueryHandler
            ->handle(
                new HappeningViewQuery(
                    $this->user,
                    $this->happening2,
                    $this->event,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn($this->expectedHappeningViews[1]);

        $this->massHandler
            ->handle(new MassUnavailabilityViewQuery($this->mass, $this->event, $this->participant, 'fr'))
            ->shouldBeCalled()
            ->willReturn($this->massView);

        $this->unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($this->unavailability, $this->event, $this->day))
            ->shouldBeCalled()
            ->willReturn($this->unavailabilityView);

        $this->meetingHandler->handle()->shouldNotBeCalled();

        $availableSlotViewQuery = new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $this->day);
        $this->availableSlotsByParticipantAndDayQueryHandler
            ->handle($availableSlotViewQuery)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->agendaCollisionManager
            ->handleCollision(
                [],
                [$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]],
                [$this->unavailabilityView],
                [$this->massView]
            )
            ->shouldBeCalled();

        $this->agendaCollisionManager
            ->getHappeningViews()
            ->shouldBeCalled()
            ->willReturn([$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]]);

        $this->agendaCollisionManager
            ->getUnavailabilityViews()
            ->shouldBeCalled()
            ->willReturn([$this->unavailabilityView]);

        $this->agendaCollisionManager
            ->getMassViews()
            ->shouldBeCalled()
            ->willReturn([$this->massView]);

        $this->agendaCollisionManager->getMeetingViews()
            ->shouldBeCalled()
            ->willReturn([]);

        $result = $this->dayViewQueryHandler->handle(new DayViewQuery(
            $this->day,
            $this->sheet,
            $this->event,
            $this->participant,
            $this->user,
            true,
            'fr',
            [$this->participation1, $this->participation2],
            [$this->unavailability],
            [$this->mass],
            [],
            []
        ));

        $this->assertEquals($this->expectedDayView, $result);
    }

    public function testHandleWithMeeting(): void
    {
        $user2       = UserFactory::create('test2@test.fr');
        $sheet2      = SheetFactory::create($this->event, $user2);
        $request     = new Request($this->sheet, [], $sheet2, [], new \DateTime(), $user2, $this->event);
        $slot        = new MeetingSlot($this->event, $this->beginHappening1, $this->endHappening1, false);
        $spot        = new Spot('ref', $this->event, 2, 3, 4, true);
        $meeting     = new Meeting($request, $slot, $this->sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $meetingView = new MeetingView(
            1,
            42,
            24,
            'userSheetTitle',
            2,
            [new SheetMetView('Sheet title', false)],
            [new MeetingOwnSheetParticipantView('Korben', 'Dallas', 'Dev')],
            $this->beginHappening1,
            $this->endHappening2,
            100,
            20,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            [],
            []
        );
        $availableSlotViews = new AvailableSlotView(1, $this->beginHappening1, $this->endHappening2);
        $meetingSlot = new MeetingSlot(
            $this->event,
            new \DateTime('2016-10-12 11:00:00'),
            new \DateTime('2016-10-12 11:20:00')
        );

        $expected = new DayView(
            $this->startTime,
            $this->endTime,
            $this->event->getConfiguration()->getScheduleScale(),
            [$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]],
            [$this->unavailabilityView],
            [$this->massView],
            [$meetingView],
            [
                '2016-10-12 11:00:00' => new TimeRangeView(
                    new \DateTime('2016-10-12 11:00:00'),
                    new \DateTime('2016-10-12 11:20:00')
                )
            ],
            [$availableSlotViews],
            null
        );

        $this->happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $this->user,
                $this->happening1,
                $this->event,
                'fr'
            )
        )->shouldBeCalled()->willReturn($this->expectedHappeningViews[0]);
        $this->happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $this->user,
                $this->happening2,
                $this->event,
                'fr'
            )
        )->shouldBeCalled()->willReturn($this->expectedHappeningViews[1]);

        $this->massHandler
            ->handle(new MassUnavailabilityViewQuery($this->mass, $this->event, $this->participant, 'fr'))
            ->shouldBeCalled()
            ->willReturn($this->massView);

        $this->unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($this->unavailability, $this->event, $this->day))
            ->shouldBeCalled()
            ->willReturn($this->unavailabilityView);

        $this->meetingHandler
            ->handle(new MeetingViewQuery($meeting, $this->sheet, true, $this->user, $this->event, 'fr'))
            ->shouldBeCalled()
            ->willReturn($meetingView);

        $availableSlotViewQuery = new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $this->day);
        $this->availableSlotsByParticipantAndDayQueryHandler
            ->handle($availableSlotViewQuery)
            ->shouldBeCalled()
            ->willReturn([$availableSlotViews]);

        $this->agendaCollisionManager
            ->handleCollision(
                [$meetingView],
                [$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]],
                [$this->unavailabilityView],
                [$this->massView]
            )
            ->shouldBeCalled();

        $this->agendaCollisionManager
            ->getHappeningViews()
            ->shouldBeCalled()
            ->willReturn([$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]]);

        $this->agendaCollisionManager
            ->getUnavailabilityViews()
            ->shouldBeCalled()
            ->willReturn([$this->unavailabilityView]);

        $this->agendaCollisionManager
            ->getMassViews()
            ->shouldBeCalled()
            ->willReturn([$this->massView]);
        $this->agendaCollisionManager->getMeetingViews()->shouldBeCalled()->willReturn([$meetingView]);

        $result = $this->dayViewQueryHandler->handle(new DayViewQuery(
            $this->day,
            $this->sheet,
            $this->event,
            $this->participant,
            $this->user,
            true,
            'fr',
            [$this->participation1, $this->participation2],
            [$this->unavailability],
            [$this->mass],
            [$meeting],
            [$meetingSlot]
        ));

        $this->assertEquals($expected, $result);
    }

    public function testHandleCancelAttendance(): void
    {
        $event     = EventFactory::createEvent();
        $user      = UserFactory::create();
        $category  = null;
        $startTime = new \DateTime('2016-10-12 10:00:00');
        $endTime   = new \DateTime('2016-10-12 18:00:00');
        $eventDay  = new TimeRangeView($startTime, $endTime);
        $sheet     = SheetFactory::create($event);
        $sheet->setAttendance(false);
        $participant  = ParticipantFactory::create($sheet);
        $massCategory = new Unavailability\Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        // Data
        $beginHappening1 = new \DateTime('2016-10-12 12:00:00');
        $beginHappening2 = new \DateTime('2016-10-12 15:30:00');
        $endHappening1   = new \DateTime('2016-10-12 14:00:00');
        $endHappening2   = new \DateTime('2016-10-12 16:50:00');
        $categoryH1      = new Happening\Category($event, 'Conference', 1, '#123123', '#123123');
        $categoryH2      = new Happening\Category($event, 'RDV', 2, '#123123', '#123123');
        $happening1      = new Happening(
            $event,
            $beginHappening1,
            $endHappening1,
            $categoryH1,
            []
        );
        $happening2 = new Happening(
            $event,
            $beginHappening2,
            $endHappening2,
            $categoryH2,
            []
        );
        $participation1 = new HappeningParticipation($happening1, $user);
        $participation2 = new HappeningParticipation($happening2, $user);
        $unavailability = new Unavailability($participant->getUser(), $event, $beginHappening2, $endHappening2);
        $mass           = new Unavailability\Mass($event, $massCategory, 'name', $beginHappening1, $endHappening1, true);

        // Expected
        $expected = new DayView(
            $startTime,
            $endTime,
            $event->getConfiguration()->getScheduleScale(),
            [],
            [],
            [],
            [],
            [],
            [],
            new CancelAttendanceUnavailabilityView($startTime, $endTime, 'Europe/Paris')
        );

        // Mock
        $this->happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $user,
                $happening1,
                $event,
                'fr'
            )
        )->shouldNotBeCalled();

        $this->massHandler->handle(new MassUnavailabilityViewQuery($mass, $event, $participant, 'fr'))->shouldNotBeCalled();
        $this->unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability, $event, $this->day))
            ->shouldNotBeCalled()
        ;
        $this->meetingHandler->handle()->shouldNotBeCalled();
        $this->cancelAttendanceUnavailabilityViewQueryHandler
            ->handle(new CancelAttendanceUnavailabilityViewQuery($event, $eventDay))
            ->shouldBeCalled()
            ->willReturn(new CancelAttendanceUnavailabilityView($startTime, $endTime, 'Europe/Paris'));

        $availableSlotViewQuery = new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $this->day);
        $this->availableSlotsByParticipantAndDayQueryHandler
            ->handle($availableSlotViewQuery)
            ->shouldNotBeCalled();

        $this->agendaCollisionManager->handleCollision([], [], [], [])->shouldBeCalled();
        $this->agendaCollisionManager->getHappeningViews()->shouldBeCalled()->willReturn([]);
        $this->agendaCollisionManager->getUnavailabilityViews()->shouldBeCalled()->willReturn([]);
        $this->agendaCollisionManager->getMassViews()->shouldBeCalled()->willReturn([]);
        $this->agendaCollisionManager->getMeetingViews()->shouldBeCalled()->willReturn([]);

        $result  = $this->dayViewQueryHandler->handle(new DayViewQuery(
            $eventDay,
            $sheet,
            $event,
            $participant,
            $user,
            false,
            'fr',
            [$participation1, $participation2],
            [$unavailability],
            [$mass],
            []
        ));
        $this->assertEquals($expected, $result);
    }

    public function testDayIsFullUnavailable(): void
    {
        $day1 = new TimeRangeView(new \DateTime('2018-10-25 10:00:00'), new \DateTime('2018-10-25 19:00:00'));
        $day2 = new TimeRangeView(new \DateTime('2018-10-26 09:00:00'), new \DateTime('2018-10-26 17:00:00'));
        $day3 = new TimeRangeView(new \DateTime('2018-10-27 11:00:00'), new \DateTime('2018-10-27 16:00:00'));

        $unavailability1 = new Unavailability(
            $this->user,
            $this->event,
            new \DateTime('2018-10-25 10:00:00'),
            new \DateTime('2018-10-25 19:00:00')
        );
        $unavailability2 = new Unavailability(
            $this->user,
            $this->event,
            new \DateTime('2018-10-26 12:00:00'),
            new \DateTime('2018-10-26 14:00:00')
        );
        $unavailability3 = new Unavailability(
            $this->user,
            $this->event,
            new \DateTime('2018-10-27 11:00:00'),
            new \DateTime('2018-10-27 13:00:00')
        );
        $unavailability4 = new Unavailability(
            $this->user,
            $this->event,
            new \DateTime('2018-10-27 12:00:00'),
            new \DateTime('2018-10-27 16:00:00')
        );

        $this
            ->availableSlotsByParticipantAndDayQueryHandler
            ->handle(new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $day1))
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this
            ->availableSlotsByParticipantAndDayQueryHandler
            ->handle(new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $day2))
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this
            ->availableSlotsByParticipantAndDayQueryHandler
            ->handle(new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $day3))
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $unavailabilityView1 = new UnavailabilityView(
            1,
            $unavailability1->getBegin(),
            $unavailability1->getEnd(),
            '',
            null,
            true,
            true
        );
        $unavailabilityView2 = new UnavailabilityView(
            2,
            $unavailability2->getBegin(),
            $unavailability2->getEnd(),
            '',
            null,
            false,
            false
        );
        $unavailabilityView3 = new UnavailabilityView(
            2,
            $unavailability3->getBegin(),
            $unavailability3->getEnd(),
            '',
            null,
            false,
            false
        );
        $unavailabilityView4 = new UnavailabilityView(
            2,
            $unavailability4->getBegin(),
            $unavailability4->getEnd(),
            '',
            null,
            true,
            true
        );

        $this
            ->unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability1, $this->event, $day1))
            ->shouldBeCalled()
            ->willReturn($unavailabilityView1)
        ;
        $this
            ->unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability2, $this->event, $day2))
            ->shouldBeCalled()
            ->willReturn($unavailabilityView2)
        ;
        $this
            ->unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability3, $this->event, $day3))
            ->shouldBeCalled()
            ->willReturn($unavailabilityView3)
        ;
        $this
            ->unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability4, $this->event, $day3))
            ->shouldBeCalled()
            ->willReturn($unavailabilityView4)
        ;

        $this
            ->agendaCollisionManager
            ->getHappeningViews()
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->agendaCollisionManager
            ->getUnavailabilityViews()
            ->shouldBeCalled()
            ->willReturn([$unavailabilityView1], [$unavailabilityView2], [$unavailabilityView3, $unavailabilityView4])
        ;

        $this->agendaCollisionManager
            ->getMassViews()
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->agendaCollisionManager
            ->getMeetingViews()
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->agendaCollisionManager
            ->handleCollision([], [], [$unavailabilityView1], [])
            ->shouldBeCalled()
        ;

        $this
            ->agendaCollisionManager
            ->handleCollision([], [], [$unavailabilityView2], [])
            ->shouldBeCalled()
        ;

        $this
            ->agendaCollisionManager
            ->handleCollision([], [], [$unavailabilityView3, $unavailabilityView4], [])
            ->shouldBeCalled()
        ;

        // Day 1 is full unavailable
        $this->assertTrue(
            $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $day1,
                    $this->sheet,
                    $this->event,
                    $this->participant,
                    $this->user,
                    false,
                    'fr',
                    [],
                    [$unavailability1, $unavailability2, $unavailability3, $unavailability4]
                )
            )->isUnavailableForThisDay
        );

        // Day 2 is not full unavailable
        $this->assertFalse(
            $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $day2,
                    $this->sheet,
                    $this->event,
                    $this->participant,
                    $this->user,
                    false,
                    'fr',
                    [],
                    [$unavailability1, $unavailability2, $unavailability3, $unavailability4]
                )
            )->isUnavailableForThisDay
        );

        // Day 3 is full unavailable
        $this->assertTrue(
            $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $day3,
                    $this->sheet,
                    $this->event,
                    $this->participant,
                    $this->user,
                    false,
                    'fr',
                    [],
                    [$unavailability1, $unavailability2, $unavailability3, $unavailability4]
                )
            )->isUnavailableForThisDay
        );
    }
}
