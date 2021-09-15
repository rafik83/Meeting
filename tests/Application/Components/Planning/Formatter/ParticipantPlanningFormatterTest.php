<?php

namespace Proximum\Vimeet\Tests\Application\Components\Planning\Formatter;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\Planning\Formatter\UnallocatedFormatter;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\SheetMetView;
use Proximum\Vimeet\Application\View\Planning\Day\AssignmentView;
use Proximum\Vimeet\Application\View\Planning\Day\HappeningParticipationView;
use Proximum\Vimeet\Application\View\Planning\Day\MassView;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Application\View\Planning\Day\UnavailabilityView;
use Proximum\Vimeet\Application\View\Planning\DayView;
use Proximum\Vimeet\Application\View\Planning\PlanningView;
use Proximum\Vimeet\Domain\Helper\LinkedSheetsTitle;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class ParticipantPlanningFormatterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $translator;

    /** @var ObjectProphecy */
    private $unallocatedFormatter;

    /** @var ObjectProphecy */
    private $planningViewQueryHandler;

    /** @var ObjectProphecy */
    private $linkedSheetsTitle;

    /** @var ObjectProphecy */
    private $requestRepository;

    public function setUp()
    {
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->unallocatedFormatter = $this->prophesize(UnallocatedFormatter::class);
        $this->planningViewQueryHandler = $this->prophesize(PlanningViewQueryHandler::class);
        $this->linkedSheetsTitle = $this->prophesize(LinkedSheetsTitle::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
    }

    public function testPreloadPlanningHandlerForUsersAndEvent()
    {
        $event = $this->prophesize(Event::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);

        $this->planningViewQueryHandler
            ->preloadForEventAndUsers($event->reveal(), [$user1->reveal(), $user2->reveal()])
            ->shouldBeCalled()
        ;

        $formatter = new ParticipantPlanningFormatter(
            $this->translator->reveal(),
            $this->unallocatedFormatter->reveal(),
            $this->planningViewQueryHandler->reveal(),
            $this->linkedSheetsTitle->reveal()
        );

        $formatter->preloadPlanningHandlerForUsersAndEvent([$user1->reveal(), $user2->reveal()], $event->reveal());
    }

    public function testPreloadPlanningHandlerForEvent()
    {
        $event = $this->prophesize(Event::class);

        $this->planningViewQueryHandler
            ->preloadForEvent($event->reveal())
            ->shouldBeCalled()
        ;

        $formatter = new ParticipantPlanningFormatter(
            $this->translator->reveal(),
            $this->unallocatedFormatter->reveal(),
            $this->planningViewQueryHandler->reveal(),
            $this->linkedSheetsTitle->reveal()
        );

        $formatter->preloadPlanningHandlerForEvent($event->reveal());
    }

    public function testFormatPlanningFromUserAndEvent()
    {
        $event = $this->prophesize(Event::class);
        $user  = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);

        $sheetMetLinkedSheet = $this->prophesize(Sheet::class);

        $sheetMetView = new SheetMetView('sheet met title', true);
        $sheetMetView2 = new SheetMetView('sheetMetLinkedSheet', false);

        $sheet->getTitle()->willReturn('user sheet title');
        $sheetMet->getTitle()->willReturn('sheet met title');
        $sheetMetLinkedSheet->getTitle()->willReturn('sheetMetLinkedSheet');

        $this->linkedSheetsTitle
            ->getSheetMetViews($sheet->reveal(), $sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetMetView, $sheetMetView2]);

        $dayBegin = new \DateTime('2017-01-01 08:00:00.000');
        $beginMass = new \DateTime('2017-01-01 10:10:00.000');
        $endMass = new \DateTime('2017-01-01 10:30:00.000');
        $unavailabilityBegin = new \DateTime('2017-01-01 12:00:00.000');
        $unavailabilityEnd = new \DateTime('2017-01-01 13:00:00.000');
        $happeningParticipationBegin = new \DateTime('2017-01-01 16:00:00.000');
        $happeningParticipationEnd = new \DateTime('2017-01-01 17:00:00.000');
        $meetingBegin = new \DateTime('2017-01-01 17:30:00.000');
        $meetingEnd = new \DateTime('2017-01-01 17:45:00.000');
        $assignmentBegin = new \DateTime('2017-01-01 13:15:00.000');
        $assignmentEnd = new \DateTime('2017-01-01 13:45:00.000');

        $mass = new MassView($beginMass, $endMass, 'mass title');
        $unavailability = new UnavailabilityView($unavailabilityBegin, $unavailabilityEnd, 'unavailability title');
        $happeningParticipation = new HappeningParticipationView(
            $happeningParticipationBegin,
            $happeningParticipationEnd,
            'happening title'
        );
        $meeting = new MeetingView(
            $meetingBegin,
            $meetingEnd,
            'spot reference',
            false,
            [],
            $sheet->reveal(),
            $sheetMet->reveal()
        );
        $assignment = new AssignmentView(
            $assignmentBegin,
            $assignmentEnd,
            'assignment mass title'
        );

        $day = $this->prophesize(DayView::class);
        $day->getTimeEntities()->willReturn([
            $mass,
            $unavailability,
            $happeningParticipation,
            $meeting,
            $assignment,
        ]);
        $day->getDay()->willReturn($dayBegin);
        $view = new PlanningView([$day->reveal()], 'Europe/Paris', true);

        $this->planningViewQueryHandler
            ->handle(new PlanningViewQuery($event->reveal(), $user->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view);

        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '11:10', '%endHour%' => '11:30'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('11:10 - 11:30 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '13:00', '%endHour%' => '14:00'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('13:00 - 14:00 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '17:00', '%endHour%' => '18:00'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('17:00 - 18:00 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '18:30', '%endHour%' => '18:45'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('18:30 - 18:45 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '14:15', '%endHour%' => '14:45'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('14:15 - 14:45 : ');
        $this->translator->trans(
            'planning.participant.meeting_multiple_sheet',
            ['%userSheet%' => 'user sheet title', '%sheetMet%' => '**sheet met title** - sheetMetLinkedSheet', '%spotRef%' => 'spot reference'],
            'messages',
            'fr'
        )->shouldBeCalled()->willReturn('spot reference - user sheet title - **sheet met title** - sheetMetLinkedSheet');

        $formatter = new ParticipantPlanningFormatter(
            $this->translator->reveal(),
            $this->unallocatedFormatter->reveal(),
            $this->planningViewQueryHandler->reveal(),
            $this->linkedSheetsTitle->reveal()
        );

        $result = $formatter->formatPlanningFromUserAndEvent($user->reveal(), $event->reveal(), 'fr');
        $expected = "**Dimanche 1 janvier 2017**\n\n- 11:10 - 11:30 : mass title\n- 13:00 - 14:00 : unavailability title\n- 14:15 - 14:45 : assignment mass title\n- 17:00 - 18:00 : happening title\n- 18:30 - 18:45 : spot reference - user sheet title - **sheet met title** - sheetMetLinkedSheet\n";

        $this->assertEquals($expected, $result);
    }

    public function testFormatPlanningFromUserAndEventNoMultipleSheet()
    {
        $event = $this->prophesize(Event::class);
        $user  = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);

        $sheetMetView = new SheetMetView('sheet met title', false);

        $sheet->getTitle()->willReturn('user sheet title');
        $sheetMet->getTitle()->willReturn('sheet met title');

        $this->linkedSheetsTitle
            ->getSheetMetViews($sheet->reveal(), $sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetMetView]);

        $dayBegin = new \DateTime('2017-01-01 08:00:00.000');
        $meetingBegin = new \DateTime('2017-01-01 17:30:00.000');
        $meetingEnd = new \DateTime('2017-01-01 17:45:00.000');

        $meeting = new MeetingView(
            $meetingBegin,
            $meetingEnd,
            'spot reference',
            false,
            [],
            $sheet->reveal(),
            $sheetMet->reveal()
        );

        $day = $this->prophesize(DayView::class);
        $day->getTimeEntities()->willReturn([$meeting]);
        $day->getDay()->willReturn($dayBegin);
        $view = new PlanningView([$day->reveal()], 'Europe/Paris', false);

        $this->planningViewQueryHandler
            ->handle(new PlanningViewQuery($event->reveal(), $user->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view);

        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '18:30', '%endHour%' => '18:45'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('18:30 - 18:45 : ');
        $this->translator->trans(
            'planning.participant.meeting',
            ['%sheetMet%' => 'sheet met title', '%spotRef%' => 'spot reference'],
            'messages',
            'fr'
        )->shouldBeCalled()->willReturn('spot reference - sheet met title');

        $formatter = new ParticipantPlanningFormatter(
            $this->translator->reveal(),
            $this->unallocatedFormatter->reveal(),
            $this->planningViewQueryHandler->reveal(),
            $this->linkedSheetsTitle->reveal()
        );

        $result = $formatter->formatPlanningFromUserAndEvent($user->reveal(), $event->reveal(), 'fr');
        $expected = "**Dimanche 1 janvier 2017**\n\n- 18:30 - 18:45 : spot reference - sheet met title\n";

        $this->assertEquals($expected, $result);
    }

    public function testFormatPlanningFromUserAndEventWithUnallocated()
    {
        $event = $this->prophesize(Event::class);
        $user  = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);

        $sheetMetLinkedSheet = $this->prophesize(Sheet::class);

        $sheetMetView = new SheetMetView('sheet met title', true);
        $sheetMetView2 = new SheetMetView('sheetMetLinkedSheet', true);

        $sheet->getTitle()->willReturn('user sheet title');
        $sheetMet->getTitle()->willReturn('sheet met title');
        $sheetMetLinkedSheet->getTitle()->willReturn('sheetMetLinkedSheet');

        $this->linkedSheetsTitle
            ->getSheetMetViews($sheet->reveal(), $sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetMetView, $sheetMetView2]);

        $dayBegin = new \DateTime('2017-01-01 08:00:00.000');
        $beginMass = new \DateTime('2017-01-01 10:10:00.000');
        $endMass = new \DateTime('2017-01-01 10:30:00.000');
        $unavailabilityBegin = new \DateTime('2017-01-01 12:00:00.000');
        $unavailabilityEnd = new \DateTime('2017-01-01 13:00:00.000');
        $happeningParticipationBegin = new \DateTime('2017-01-01 16:00:00.000');
        $happeningParticipationEnd = new \DateTime('2017-01-01 17:00:00.000');
        $meetingBegin = new \DateTime('2017-01-01 17:30:00.000');
        $meetingEnd = new \DateTime('2017-01-01 17:45:00.000');
        $assignmentBegin = new \DateTime('2017-01-01 13:15:00.000');
        $assignmentEnd = new \DateTime('2017-01-01 13:45:00.000');

        $mass = new MassView($beginMass, $endMass, 'mass title');
        $unavailability = new UnavailabilityView($unavailabilityBegin, $unavailabilityEnd, 'unavailability title');
        $happeningParticipation = new HappeningParticipationView(
            $happeningParticipationBegin,
            $happeningParticipationEnd,
            'happening title'
        );
        $meeting = new MeetingView(
            $meetingBegin,
            $meetingEnd,
            'spot reference',
            false,
            [],
            $sheet->reveal(),
            $sheetMet->reveal()
        );
        $assignment = new AssignmentView(
            $assignmentBegin,
            $assignmentEnd,
            'assignment mass title'
        );

        $day = $this->prophesize(DayView::class);
        $day->getTimeEntities()->willReturn([
            $mass,
            $unavailability,
            $happeningParticipation,
            $meeting,
            $assignment,
        ]);
        $day->getDay()->willReturn($dayBegin);
        $view = new PlanningView([$day->reveal()], 'Europe/Paris', true);

        $this->planningViewQueryHandler
            ->handle(new PlanningViewQuery($event->reveal(), $user->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view);

        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '11:10', '%endHour%' => '11:30'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('11:10 - 11:30 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '13:00', '%endHour%' => '14:00'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('13:00 - 14:00 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '17:00', '%endHour%' => '18:00'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('17:00 - 18:00 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '18:30', '%endHour%' => '18:45'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('18:30 - 18:45 : ');
        $this->translator->trans(
            ParticipantPlanningFormatter::TRANSLATE_TIME_ENTITY,
            ['%beginHour%' => '14:15', '%endHour%' => '14:45'],
            ParticipantPlanningFormatter::TRANSLATION_DOMAIN,
            'fr'
        )->shouldBeCalled()->willReturn('14:15 - 14:45 : ');
        $this->translator->trans(
            'planning.participant.meeting_multiple_sheet',
            ['%userSheet%' => 'user sheet title', '%sheetMet%' => '**sheet met title** - **sheetMetLinkedSheet**', '%spotRef%' => 'spot reference'],
            'messages',
            'fr'
        )->shouldBeCalled()->willReturn('spot reference - user sheet title - **sheet met title** - **sheetMetLinkedSheet**');

        $this->unallocatedFormatter
            ->formatForUser($event->reveal(), $user->reveal(), 'fr', true)
            ->shouldBeCalled()
            ->willReturn('unallocated: sheet met 1');

        $formatter = new ParticipantPlanningFormatter(
            $this->translator->reveal(),
            $this->unallocatedFormatter->reveal(),
            $this->planningViewQueryHandler->reveal(),
            $this->linkedSheetsTitle->reveal()
        );

        $result = $formatter->formatPlanningFromUserAndEventWithUnallocated($user->reveal(), $event->reveal(), 'fr');
        $expected = "**Dimanche 1 janvier 2017**\n\n- 11:10 - 11:30 : mass title\n- 13:00 - 14:00 : unavailability title\n- 14:15 - 14:45 : assignment mass title\n- 17:00 - 18:00 : happening title\n- 18:30 - 18:45 : spot reference - user sheet title - **sheet met title** - **sheetMetLinkedSheet**\n\n\nunallocated: sheet met 1";

        $this->assertEquals($expected, $result);
    }
}
