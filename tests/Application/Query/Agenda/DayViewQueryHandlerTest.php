<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQueryHandler;
use Proximum\Vimeet\Application\Components\Agenda\AgendaCollisionManager;
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
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
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
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

class DayViewQueryHandlerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var Category */
    private $category;

    /** @var User */
    private $user;

    /** @var Day */
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

    public function setUp()
    {
        $this->currentTime = new \DateTime('2016-10-12 15:00:00');
        $this->event = EventFactory::createEvent();
        $this->category = null;
        $this->user = UserFactory::create();
        $this->startTime = new \DateTime('2016-10-12 10:00:00');
        $this->endTime = new \DateTime('2016-10-12 18:00:00');
        $this->day = new Day($this->event, $this->startTime, $this->endTime);
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
        $this->unavailabilityView = new UnavailabilityView(1, $this->beginHappening2, $this->endHappening2, 'Europe/Paris');
        $this->expectedDayView = new DayView(
            $this->startTime,
            $this->endTime,
            $this->event->getConfiguration()->getScheduleScale(),
            [$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]],
            [$this->unavailabilityView],
            [$this->massView],
            [],
            []
        );

        $this->agendaCollisionManager = $this->prophesize(AgendaCollisionManager::class);
        $this->availableSlotsByParticipantAndDayQueryHandler = $this->prophesize(AvailableSlotsByParticipantAndDayQueryHandler::class);
        $this->cancelAttendanceUnavailabilityViewQueryHandler = $this->prophesize(CancelAttendanceUnavailabilityViewQueryHandler::class);
    }
    
    public function testHandle()
    {
        // Mock
        $happeningViewQueryHandler = $this->prophesize(HappeningViewQueryHandler::class);

        $happeningViewQueryHandler
            ->handle(
                new HappeningViewQuery(
                    $this->happening1,
                    $this->event,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn($this->expectedHappeningViews[0]);

        $happeningViewQueryHandler
            ->handle(
                new HappeningViewQuery(
                    $this->happening2,
                    $this->event,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn($this->expectedHappeningViews[1]);

        $massHandler           = $this->prophesize(MassUnavailabilityViewQueryHandler::class);
        $unavailabilityHandler = $this->prophesize(UnavailabilityViewQueryHandler::class);
        $massHandler
            ->handle(new MassUnavailabilityViewQuery($this->mass, $this->event, $this->participant, 'fr'))
            ->shouldBeCalled()
            ->willReturn($this->massView);

        $unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($this->unavailability, $this->event, $this->day))
            ->shouldBeCalled()
            ->willReturn($this->unavailabilityView);

        $meetingHandler = $this->prophesize(MeetingViewQueryHandler::class);
        $meetingHandler->handle()->shouldNotBeCalled();

        $availableSlotViewQuery = new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $this->day);
        $this->availableSlotsByParticipantAndDayQueryHandler
            ->handle($availableSlotViewQuery)
            ->shouldBeCalled()
            ->willReturn([]);

        $handler = new DayViewQueryHandler(
            $happeningViewQueryHandler->reveal(),
            $unavailabilityHandler->reveal(),
            $massHandler->reveal(),
            $meetingHandler->reveal(),
            $this->cancelAttendanceUnavailabilityViewQueryHandler->reveal(),
            $this->availableSlotsByParticipantAndDayQueryHandler->reveal(),
            $this->agendaCollisionManager->reveal()
        );

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


        $result = $handler->handle(new DayViewQuery(
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
            []
        ));

        $this->assertEquals($this->expectedDayView, $result);
    }

    public function testHandleWithMeeting()
    {
        $user2       = UserFactory::create('test2@test.fr');
        $sheet2      = SheetFactory::create($this->event, $user2);
        $request     = new Request($this->sheet, [], $sheet2, [], new \DateTime(), $user2, $this->event);
        $slot        = new MeetingSlot($this->event, $this->beginHappening1, $this->endHappening1, false);
        $spot        = new Spot('ref', $this->event, 2, 3, 4, true);
        $meeting     = new Meeting($request, $slot, $this->sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $meetingView = new MeetingView(
            1,
            'userSheetTitle',
            2,
            'title',
            $this->beginHappening1,
            $this->endHappening2,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            []
        );
        $availableSlotViews = new AvailableSlotView(1, $this->beginHappening1, $this->endHappening2);

        $expected = new DayView(
            $this->startTime,
            $this->endTime,
            $this->event->getConfiguration()->getScheduleScale(),
            [$this->expectedHappeningViews[0], $this->expectedHappeningViews[1]],
            [$this->unavailabilityView],
            [$this->massView],
            [$meetingView],
            [$availableSlotViews],
            null
        );


        // Mock
        $happeningViewQueryHandler = $this->prophesize(HappeningViewQueryHandler::class);
        $happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $this->happening1,
                $this->event,
                'fr'
            )
        )->shouldBeCalled()->willReturn($this->expectedHappeningViews[0]);
        $happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $this->happening2,
                $this->event,
                'fr'
            )
        )->shouldBeCalled()->willReturn($this->expectedHappeningViews[1]);

        $massHandler           = $this->prophesize(MassUnavailabilityViewQueryHandler::class);
        $unavailabilityHandler = $this->prophesize(UnavailabilityViewQueryHandler::class);
        $massHandler
            ->handle(new MassUnavailabilityViewQuery($this->mass, $this->event, $this->participant, 'fr'))
            ->shouldBeCalled()
            ->willReturn($this->massView);

        $unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($this->unavailability, $this->event, $this->day))
            ->shouldBeCalled()
            ->willReturn($this->unavailabilityView);

        $meetingHandler = $this->prophesize(MeetingViewQueryHandler::class);
        $meetingHandler
            ->handle(new MeetingViewQuery($meeting, $this->sheet, true, $this->user, $this->event, 'fr'))
            ->shouldBeCalled()
            ->willReturn($meetingView);

        $availableSlotViewQuery = new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $this->day);
        $this->availableSlotsByParticipantAndDayQueryHandler
            ->handle($availableSlotViewQuery)
            ->shouldBeCalled()
            ->willReturn([$availableSlotViews]);

        $handler = new DayViewQueryHandler(
            $happeningViewQueryHandler->reveal(),
            $unavailabilityHandler->reveal(),
            $massHandler->reveal(),
            $meetingHandler->reveal(),
            $this->cancelAttendanceUnavailabilityViewQueryHandler->reveal(),
            $this->availableSlotsByParticipantAndDayQueryHandler->reveal(),
            $this->agendaCollisionManager->reveal()
        );

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

        $result  = $handler->handle(new DayViewQuery(
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
            [$meeting]
        ));

        $this->assertEquals($expected, $result);
    }

    public function testHandleCancelAttendance()
    {
        $event     = EventFactory::createEvent();
        $user      = UserFactory::create();
        $category  = null;
        $startTime = new \DateTime('2016-10-12 10:00:00');
        $endTime   = new \DateTime('2016-10-12 18:00:00');
        $eventDay  = new Day($event, $startTime, $endTime);
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
            new CancelAttendanceUnavailabilityView($startTime, $endTime, 'Europe/Paris')
        );
        // Mock
        $happeningViewQueryHandler = $this->prophesize(HappeningViewQueryHandler::class);
        $happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $happening1,
                $event,
                'fr'
            )
        )->shouldNotBeCalled();

        $massHandler           = $this->prophesize(MassUnavailabilityViewQueryHandler::class);
        $unavailabilityHandler = $this->prophesize(UnavailabilityViewQueryHandler::class);
        $massHandler->handle(new MassUnavailabilityViewQuery($mass, $event, $participant, 'fr'))->shouldNotBeCalled();
        $unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability, $event, $this->day))
            ->shouldNotBeCalled()
        ;
        $meetingHandler = $this->prophesize(MeetingViewQueryHandler::class);
        $meetingHandler->handle()->shouldNotBeCalled();
        $this->cancelAttendanceUnavailabilityViewQueryHandler
            ->handle(new CancelAttendanceUnavailabilityViewQuery($event, $eventDay))
            ->shouldBeCalled()
            ->willReturn(new CancelAttendanceUnavailabilityView($startTime, $endTime, 'Europe/Paris'));

        $handler = new DayViewQueryHandler(
            $happeningViewQueryHandler->reveal(),
            $unavailabilityHandler->reveal(),
            $massHandler->reveal(),
            $meetingHandler->reveal(),
            $this->cancelAttendanceUnavailabilityViewQueryHandler->reveal(),
            $this->availableSlotsByParticipantAndDayQueryHandler->reveal(),
            $this->agendaCollisionManager->reveal()
        );

        $availableSlotViewQuery = new AvailableSlotsByParticipantAndDayQuery($this->event, $this->participant, $this->day);
        $this->availableSlotsByParticipantAndDayQueryHandler
            ->handle($availableSlotViewQuery)
            ->shouldNotBeCalled();

        $this->agendaCollisionManager->handleCollision([], [], [], [])->shouldBeCalled();
        $this->agendaCollisionManager->getHappeningViews()->shouldBeCalled()->willReturn([]);
        $this->agendaCollisionManager->getUnavailabilityViews()->shouldBeCalled()->willReturn([]);
        $this->agendaCollisionManager->getMassViews()->shouldBeCalled()->willReturn([]);
        $this->agendaCollisionManager->getMeetingViews()->shouldBeCalled()->willReturn([]);

        $result  = $handler->handle(new DayViewQuery(
            $eventDay,
            $sheet,
            $event,
            $participant,
            $user,
            false,
            'fr',
            [$participation1, $participation2],
            [$unavailability],
            [$mass]
        ));
        $this->assertEquals($expected, $result);
    }
}
