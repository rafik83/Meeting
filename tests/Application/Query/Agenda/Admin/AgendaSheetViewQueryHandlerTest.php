<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaSheetViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaSheetViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;
use Proximum\Vimeet\Application\View\Agenda\AgendaSheetIndicatorView;
use Proximum\Vimeet\Application\View\Agenda\AgendaSheetView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Planner\IndicatorView;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\MeetingFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class AgendaSheetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $sheet       = SheetFactory::create($event);
        $sheet2      = SheetFactory::create($event);
        $user        = UserFactory::create();
        $meeting     = MeetingFactory::createMeeting();
        $participant = ParticipantFactory::create($sheet);
        $locale      = 'fr';
        $date        = new \DateTime();
        $begin       = new \DateTime('2016-10-12 12:00:00');
        $end         = new \DateTime('2016-10-12 15:30:00');

        $agendaParticipantViewQueryHandler = $this->prophesize(AgendaParticipantViewQueryHandler::class);
        $happeningParticipationRepository  = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $unavailabilityRepository          = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $massUnavailabilityRepository      = $this->prophesize(MassRepositoryInterface::class);
        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepository                 = $this->prophesize(RequestRepositoryInterface::class);
        $requestViewQueryHandler           = $this->prophesize(RequestViewQueryHandler::class);
        $massAssignmentRepository          = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $indicatorCalculator               = $this->prophesize(IndicatorCalculator::class);

        $happeningCategory      = new Happening\Category($event, 'picto', 1, 'black', 'black');
        $unavailabilityCategory = new Category($event, 'picto', 'title', 'black', 'black');
        $mass                   = new Mass($event, $unavailabilityCategory, 'name', $begin, $end, true);
        $assignment             = new MassAssignment($mass, $user, $begin, $end);
        $unavailability         = new Unavailability($user, $event, $begin, $end);

        $happeningParticipation = new HappeningParticipation(
            new Happening($event, $begin, $end, $happeningCategory, []),
            $user
        );

        $meetingRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([$meeting]);
        $happeningParticipationRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([$happeningParticipation]);
        $massAssignmentRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([$assignment]);
        $massUnavailabilityRepository->findBlockingByEvent($event)->shouldBeCalled()->willReturn([$mass]);
        $unavailabilityRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([$unavailability]);

        $queryTest = new AgendaParticipantViewQuery(
            $participant,
            $event,
            $sheet,
            $locale,
            [$happeningParticipation],
            [$unavailability],
            [$mass],
            [$meeting],
            [$assignment]
        );

        $agendaParticipantViewQueryHandler->handle($queryTest)->shouldBeCalled()->willReturn($participant);

        $request1 = new Request($sheet, [], $sheet, [], $date, $user, $event);
        $request2 = new Request($sheet2, [], $sheet, [], $date, $user, $event);
        $requestRepository
            ->getUnassignedRequestsBySheetAndEvent($sheet, Request::STATE_APPROVED)
            ->shouldBeCalled()
            ->willReturn([$request1, $request2]);

        $requestView1      = new RequestView(1, 'zzz', 1, [], true, false, true, true);
        $requestView2      = new RequestView(2, 'aaa', 2, [], true, false, true, true);
        $requestViewQuery1 = new RequestViewQuery(
            $request1,
            $sheet,
            $locale
        );
        $requestViewQuery2 = new RequestViewQuery(
            $request2,
            $sheet,
            $locale
        );

        $requestViewQueryHandler
            ->handle($requestViewQuery1)
            ->shouldBeCalled()
            ->willReturn($requestView1);

        $requestViewQueryHandler
            ->handle($requestViewQuery2)
            ->shouldBeCalled()
            ->willReturn($requestView2);

        $expectedAgendaSheetView = new AgendaSheetView(
            [$participant],
            [$requestView2, $requestView1],
            new AgendaSheetIndicatorView(10, 1)
        );

        $indicatorCalculator->getIndicator($sheet)->shouldBeCalled()->willReturn(
            new IndicatorView(12, 1, 0, 1, 10, 0, 2, null, null)
        );

        $handler = new AgendaSheetViewQueryHandler(
            $agendaParticipantViewQueryHandler->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $meetingRepository->reveal(),
            $requestRepository->reveal(),
            $requestViewQueryHandler->reveal(),
            $massAssignmentRepository->reveal(),
            $indicatorCalculator->reveal()
        );

        $this->assertEquals($expectedAgendaSheetView, $handler->handle(new AgendaSheetViewQuery($sheet, $locale)));
    }
}
