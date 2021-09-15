<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planning;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planning\DayViewQuery;
use Proximum\Vimeet\Application\Query\Planning\DayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\DayView;
use Proximum\Vimeet\Application\View\Planning\PlanningView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Mass\FilterMassUnavailabilitiesBySheetsType;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PlanningViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $user      = $this->prophesize(User::class);
        $locale    = 'de';
        $beginDay1 = new \DateTime('2016-10-12 10:00');
        $endDay1   = new \DateTime('2016-10-12 18:00');
        $beginDay2 = new \DateTime('2016-10-13 10:00');
        $endDay2   = new \DateTime('2016-10-13 18:00');
        $day1      = new Day($event, $beginDay1, $endDay1);
        $day2      = new Day($event, $beginDay2, $endDay2);

        $sheet = $this->prophesize(Sheet::class);

        $category        = $this->prophesize(Category::class);
        $begin1          = new \DateTime('2016-10-12 11:00');
        $end1            = new \DateTime('2016-10-12 12:30');
        $begin2          = new \DateTime('2016-10-13 12:00');
        $end2            = new \DateTime('2016-10-13 13:30');
        $begin3          = new \DateTime('2016-10-12 14:00');
        $end3            = new \DateTime('2016-10-12 14:30');
        $mass1           = new Mass($event, $category->reveal(), 'mass1', $begin1, $end1, true, false);
        $mass2           = new Mass($event, $category->reveal(), 'mass2', $begin2, $end2, true, false);
        $mass3           = new Mass($event, $category->reveal(), 'mass3', $begin3, $end3, true, true);
        $assignment      = new MassAssignment($mass3, $user->reveal(), $begin3, $end3);
        $meeting         = $this->prophesize(Meeting::class);
        $happening       = $this->prophesize(HappeningParticipation::class);
        $unavailability1 = $this->prophesize(Unavailability::class);
        $unavailability2 = $this->prophesize(Unavailability::class);

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day1, $day2]);

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository
            ->findByUser($user->reveal(), $event, true)
            ->shouldBeCalled()
            ->willReturn([$happening->reveal()]);

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository
            ->findByUserAndEvent($user->reveal(), $event)
            ->shouldBeCalled()
            ->willReturn([$unavailability1->reveal(), $unavailability2->reveal()]);

        $massUnavailabilityRepository = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findNotDispatchedByEvent($event)->shouldBeCalled()->willReturn([$mass1, $mass2]);

        $assignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $assignmentRepository
            ->findEnabledByUserAndEvent($user->reveal(), $event)
            ->shouldBeCalled()
            ->willReturn([$assignment]);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository
            ->findByUserAndEvent($user->reveal(), $event)
            ->shouldBeCalled()
            ->willReturn([$meeting->reveal()]);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findWithEnabledSheetByEvent($event)->shouldNotBeCalled();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByUserAndEventWhereUserIsParticipant($user->reveal(), $event)
            ->shouldBeCalled()
            ->willReturn([$sheet->reveal()])
        ;

        $dayViewQueryHandler = $this->prophesize(DayViewQueryHandler::class);

        $dayViewQueryHandler
            ->handle(
                new DayViewQuery(
                    $event,
                    $user->reveal(),
                    $day1,
                    'fr',
                    [$unavailability1->reveal(), $unavailability2->reveal()],
                    [$happening->reveal()],
                    [$mass2],
                    [$assignment],
                    [$meeting->reveal()]
                )
            )
            ->shouldBeCalled()
            ->willReturn(new DayView($beginDay1, $endDay1, [], [], [], [], []));

        $dayViewQueryHandler
            ->handle(
                new DayViewQuery(
                    $event,
                    $user->reveal(),
                    $day2,
                    'fr',
                    [$unavailability1->reveal(), $unavailability2->reveal()],
                    [$happening->reveal()],
                    [$mass2],
                    [$assignment],
                    [$meeting->reveal()]
                )
            )
            ->shouldBeCalled()
            ->willReturn(new DayView($beginDay2, $endDay2, [], [], [], [], []));

        $filterMassUnavailabilitiesBySheetsType = $this->prophesize(FilterMassUnavailabilitiesBySheetsType::class);
        $filterMassUnavailabilitiesBySheetsType
            ->handle([$mass1, $mass2], [$sheet->reveal()])
            ->shouldBeCalled()
            ->willReturn([$mass2])
        ;

        $query   = new PlanningViewQuery($event, $user->reveal(), $locale);
        $handler = new PlanningViewQueryHandler(
            $dayRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $assignmentRepository->reveal(),
            $meetingRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $userRepository->reveal(),
            $sheetRepository->reveal(),
            $filterMassUnavailabilitiesBySheetsType->reveal()
        );
        $result  = $handler->handle($query);

        // Expected
        $expected = new PlanningView(
            [
                new DayView($beginDay1, $endDay1, [], [], [], [], []),
                new DayView($beginDay2, $endDay2, [], [], [], [], []),
            ],
            'Europe/Paris',
            false
        );

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testHandlePreloadForUsers()
    {
        $event = EventFactory::createEvent();
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(1);
        $participant = $this->prophesize(Participant::class);
        $locale    = 'fr';
        $beginDay1 = new \DateTime('2016-10-12 10:00');
        $endDay1   = new \DateTime('2016-10-12 18:00');
        $beginDay2 = new \DateTime('2016-10-13 10:00');
        $endDay2   = new \DateTime('2016-10-13 18:00');
        $day1      = new Day($event, $beginDay1, $endDay1);
        $day2      = new Day($event, $beginDay2, $endDay2);

        $category = $this->prophesize(Category::class);
        $begin1   = new \DateTime('2016-10-12 11:00');
        $end1     = new \DateTime('2016-10-12 12:30');
        $begin2   = new \DateTime('2016-10-13 12:00');
        $end2     = new \DateTime('2016-10-13 13:30');
        $begin3   = new \DateTime('2016-10-12 14:00');
        $end3     = new \DateTime('2016-10-12 14:30');

        $mass1 = new Mass($event, $category->reveal(), 'mass1', $begin1, $end1, true, false);
        $mass2 = new Mass($event, $category->reveal(), 'mass2', $begin2, $end2, true, false);
        $mass3 = new Mass($event, $category->reveal(), 'mass3', $begin3, $end3, true, true);

        $assignment = new MassAssignment($mass3, $user->reveal(), $begin3, $end3);

        $meeting = $this->prophesize(Meeting::class);
        $meeting->getAllParticipants()->willReturn([$participant->reveal()]);

        $user1 = $this->prophesize(User::class);
        $user1->getId()->willReturn(1);

        $happening = $this->prophesize(HappeningParticipation::class);
        $happening->getUser()->willReturn($user1->reveal());

        $unavailability1 = $this->prophesize(Unavailability::class);
        $unavailability1->getUser()->willReturn($user1->reveal());

        $unavailability2 = $this->prophesize(Unavailability::class);
        $unavailability2->getUser()->willReturn($user1->reveal());

        $participant->getId()->willReturn(1234);
        $participant->getUser()->willReturn($user1->reveal());

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day1, $day2]);

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository
            ->findByEventAndUsers($event, [$user->reveal(), $user1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$happening->reveal()]);

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository
            ->findByEventAndUsers($event, [$user->reveal(), $user1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$unavailability1->reveal(), $unavailability2->reveal()]);

        $massUnavailabilityRepository = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository
            ->findNotDispatchedByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$mass1, $mass2]);

        $assignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $assignmentRepository
            ->findEnabledByEventAndUsers($event, [$user->reveal(), $user1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$assignment]);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository
            ->findByEventAndUsers($event, [$user->reveal(), $user1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$meeting->reveal()]);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findWithEnabledSheetByEvent($event)->shouldNotBeCalled();

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByUserAndEventWhereUserIsParticipant($user->reveal(), $event)
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;

        $dayViewQueryHandler = $this->prophesize(DayViewQueryHandler::class);

        $dayViewQueryHandler
            ->handle(
                new DayViewQuery(
                    $event,
                    $user->reveal(),
                    $day1,
                    $locale,
                    [$unavailability1->reveal(), $unavailability2->reveal()],
                    [$happening->reveal()],
                    [$mass1, $mass2],
                    [$assignment],
                    [$meeting->reveal()]
                )
            )
            ->shouldBeCalled()
            ->willReturn(new DayView($beginDay1, $endDay1, [], [], [], [], []));

        $dayViewQueryHandler
            ->handle(
                new DayViewQuery(
                    $event,
                    $user->reveal(),
                    $day2,
                    $locale,
                    [$unavailability1->reveal(), $unavailability2->reveal()],
                    [$happening->reveal()],
                    [$mass1, $mass2],
                    [$assignment],
                    [$meeting->reveal()]
                )
            )
            ->shouldBeCalled()
            ->willReturn(new DayView($beginDay2, $endDay2, [], [], [], [], []));

        $filterMassUnavailabilitiesBySheetsType = $this->prophesize(FilterMassUnavailabilitiesBySheetsType::class);
        $filterMassUnavailabilitiesBySheetsType
            ->handle([$mass1, $mass2], [$sheet1->reveal(), $sheet2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$mass1, $mass2])
        ;

        $handler = new PlanningViewQueryHandler(
            $dayRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $assignmentRepository->reveal(),
            $meetingRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $userRepository->reveal(),
            $sheetRepository->reveal(),
            $filterMassUnavailabilitiesBySheetsType->reveal()
        );
        $users = [$user->reveal(), $user1->reveal()];
        $handler->preloadForEventAndUsers($event, $users);
        $result = $handler->handle(new PlanningViewQuery($event, $user->reveal(), $locale));

        // Expected
        $expected = new PlanningView(
            [
                new DayView($beginDay1, $endDay1, [], [], [], [], []),
                new DayView($beginDay2, $endDay2, [], [], [], [], []),
            ],
            'Europe/Paris',
            true
        );

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testHandlePreloadForEvent()
    {
        $event       = EventFactory::createEvent();
        $participant = $this->prophesize(Participant::class);
        $user1       = $this->prophesize(User::class);
        $user1->getId()->willReturn(1);
        $participant->getUser()->willReturn($user1->reveal());
        $locale    = 'fr';
        $beginDay1 = new \DateTime('2016-10-12 10:00');
        $endDay1   = new \DateTime('2016-10-12 18:00');
        $beginDay2 = new \DateTime('2016-10-13 10:00');
        $endDay2   = new \DateTime('2016-10-13 18:00');
        $day1      = new Day($event, $beginDay1, $endDay1);
        $day2      = new Day($event, $beginDay2, $endDay2);

        $category = $this->prophesize(Category::class);
        $begin1   = new \DateTime('2016-10-12 11:00');
        $end1     = new \DateTime('2016-10-12 12:30');
        $begin2   = new \DateTime('2016-10-13 12:00');
        $end2     = new \DateTime('2016-10-13 13:30');
        $begin3   = new \DateTime('2016-10-12 14:00');
        $end3     = new \DateTime('2016-10-12 14:30');

        $mass1 = new Mass($event, $category->reveal(), 'mass1', $begin1, $end1, true, false);
        $mass2 = new Mass($event, $category->reveal(), 'mass2', $begin2, $end2, true, false);
        $mass3 = new Mass($event, $category->reveal(), 'mass3', $begin3, $end3, true, true);

        $assignment = new MassAssignment($mass3, $user1->reveal(), $begin3, $end3);

        $meeting = $this->prophesize(Meeting::class);
        $meeting->getAllParticipants()->willReturn([$participant->reveal()]);

        $user1 = $this->prophesize(User::class);
        $user1->getId()->willReturn(1);

        $happening = $this->prophesize(HappeningParticipation::class);
        $happening->getUser()->willReturn($user1->reveal());

        $unavailability1 = $this->prophesize(Unavailability::class);
        $unavailability1->getUser()->willReturn($user1->reveal());

        $unavailability2 = $this->prophesize(Unavailability::class);
        $unavailability2->getUser()->willReturn($user1->reveal());

        $participant->getId()->willReturn(1234);
        $participant->getUser()->willReturn($user1->reveal());

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day1, $day2]);

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->getByEvent($event)->shouldBeCalled()->willReturn([$happening->reveal()]);

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->getByEvent($event)->shouldBeCalled()->willReturn([
            $unavailability1->reveal(),
            $unavailability2->reveal(),
        ]);

        $massUnavailabilityRepository = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findNotDispatchedByEvent($event)->shouldBeCalled()->willReturn([$mass1, $mass2]);

        $assignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $assignmentRepository->findEnabledByEvent($event)->shouldBeCalled()->willReturn([$assignment]);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->getAllByEvent($event)->shouldBeCalled()->willReturn([$meeting->reveal()]);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findWithEnabledSheetByEvent($event)->shouldBeCalled()->willReturn([$user1]);

        $sheet = $this->prophesize(Sheet::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByUserAndEventWhereUserIsParticipant($user1->reveal(), $event)
            ->shouldBeCalled()
            ->willReturn([$sheet->reveal()])
        ;

        $dayViewQueryHandler = $this->prophesize(DayViewQueryHandler::class);
        $dayView1            = new DayView($beginDay1, $endDay1, [], [], [], [], []);
        $dayView2            = new DayView($beginDay2, $endDay2, [], [], [], [], []);

        $dayViewQueryHandler
            ->handle(
                new DayViewQuery(
                    $event,
                    $user1->reveal(),
                    $day1,
                    $locale,
                    [$unavailability1->reveal(), $unavailability2->reveal()],
                    [$happening->reveal()],
                    [$mass1, $mass2],
                    [$assignment],
                    [$meeting->reveal()]
                )
            )
            ->shouldBeCalled()
            ->willReturn($dayView1);

        $dayViewQueryHandler
            ->handle(
                new DayViewQuery(
                    $event,
                    $user1->reveal(),
                    $day2,
                    $locale,
                    [$unavailability1->reveal(), $unavailability2->reveal()],
                    [$happening->reveal()],
                    [$mass1, $mass2],
                    [$assignment],
                    [$meeting->reveal()]
                )
            )
            ->shouldBeCalled()
            ->willReturn($dayView2);

        $filterMassUnavailabilitiesBySheetsType = $this->prophesize(FilterMassUnavailabilitiesBySheetsType::class);
        $filterMassUnavailabilitiesBySheetsType
            ->handle([$mass1, $mass2], [$sheet->reveal()])
            ->shouldBeCalled()
            ->willReturn([$mass1, $mass2])
        ;

        $query   = new PlanningViewQuery($event, $user1->reveal(), $locale);
        $handler = new PlanningViewQueryHandler(
            $dayRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $assignmentRepository->reveal(),
            $meetingRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $userRepository->reveal(),
            $sheetRepository->reveal(),
            $filterMassUnavailabilitiesBySheetsType->reveal()
        );
        $handler->preloadForEvent($event);
        $result = $handler->handle($query);

        // Expected
        $expected = new PlanningView([$dayView1, $dayView2], 'Europe/Paris', false);

        // Assert
        $this->assertEquals($expected, $result);
    }
}
