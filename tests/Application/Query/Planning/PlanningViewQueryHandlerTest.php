<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Planning;

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
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PlanningViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $sheet       = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $locale      = 'fr';
        $beginDay1   = new \DateTime('2016-10-12 10:00');
        $endDay1     = new \DateTime('2016-10-12 18:00');
        $beginDay2   = new \DateTime('2016-10-13 10:00');
        $endDay2     = new \DateTime('2016-10-13 18:00');
        $day1        = new Day($event, $beginDay1, $endDay1);
        $day2        = new Day($event, $beginDay2, $endDay2);

        $category = $this->prophesize(Category::class);
        $begin1 = new \DateTime('2016-10-12 11:00');
        $end1   = new \DateTime('2016-10-12 12:30');
        $begin2 = new \DateTime('2016-10-13 12:00');
        $end2   = new \DateTime('2016-10-13 13:30');
        $begin3 = new \DateTime('2016-10-12 14:00');
        $end3   = new \DateTime('2016-10-12 14:30');
        $mass1  = new Mass($event, $category->reveal(), 'mass1', $begin1, $end1, true, false);
        $mass2 = new Mass($event, $category->reveal(), 'mass2', $begin2, $end2, true, false);
        $mass3 = new Mass($event, $category->reveal(), 'mass3', $begin3, $end3, true, true);
        $assignment = new MassAssignment($mass3, $participant->reveal(), $begin3, $end3);
        $meeting = $this->prophesize(Meeting::class);
        $happening = $this->prophesize(HappeningParticipation::class);
        $unavailability1 = $this->prophesize(Unavailability::class);
        $unavailability2 = $this->prophesize(Unavailability::class);

        $participant->getId()->willReturn(1234);
        $participant->getSheet()->willReturn($sheet);
        $sheet->getEvent()->willReturn($event);

        // Mock
        $dayRepository                    = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day1, $day2]);
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->findByParticipant($participant->reveal())->shouldBeCalled()->willReturn([$happening->reveal()]);
        $unavailabilityRepository         = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->findByParticipant($participant->reveal())->shouldBeCalled()->willReturn([$unavailability1->reveal(), $unavailability2->reveal()]);

        $massUnavailabilityRepository     = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findNotDispatchedByEvent($event)->shouldBeCalled()->willReturn([$mass1, $mass2]);

        $assignmentRepository             = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $assignmentRepository->findEnabledByParticipant($participant->reveal())->shouldBeCalled()->willReturn([$assignment]);

        $meetingRepository                = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->findByParticipant($participant->reveal())->shouldBeCalled()->willReturn([$meeting->reveal()]);

        $dayViewQueryHandler              = $this->prophesize(DayViewQueryHandler::class);
        $dayView1 = new DayView($beginDay1, $endDay1, [], [], [], [], []);
        $dayView2 = new DayView($beginDay2, $endDay2, [], [], [], [], []);
        $dayViewQueryHandler->handle(
            new DayViewQuery(
                $sheet->reveal(),
                $day1,
                $locale,
                [$unavailability1->reveal(), $unavailability2],
                [$happening->reveal()],
                [$mass1, $mass2],
                [$assignment],
                [$meeting->reveal()]
            )
        )->shouldBeCalled()->willReturn($dayView1);
        $dayViewQueryHandler->handle(
            new DayViewQuery(
                $sheet->reveal(),
                $day2,
                $locale,
                [$unavailability1->reveal(), $unavailability2],
                [$happening->reveal()],
                [$mass1, $mass2],
                [$assignment],
                [$meeting->reveal()]
            )
        )->shouldBeCalled()->willReturn($dayView2);

        $query   = new PlanningViewQuery($event, $participant->reveal(), $locale);
        $handler = new PlanningViewQueryHandler(
            $dayRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $assignmentRepository->reveal(),
            $meetingRepository->reveal(),
            $dayViewQueryHandler->reveal()
        );
        $result = $handler->handle($query);

        // Expected
        $expected = new PlanningView([$dayView1, $dayView2]);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testHandlePreloadForParticipants()
    {
        $event       = EventFactory::createEvent();
        $sheet       = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $locale      = 'fr';
        $beginDay1   = new \DateTime('2016-10-12 10:00');
        $endDay1     = new \DateTime('2016-10-12 18:00');
        $beginDay2   = new \DateTime('2016-10-13 10:00');
        $endDay2     = new \DateTime('2016-10-13 18:00');
        $day1        = new Day($event, $beginDay1, $endDay1);
        $day2        = new Day($event, $beginDay2, $endDay2);

        $category = $this->prophesize(Category::class);
        $begin1 = new \DateTime('2016-10-12 11:00');
        $end1   = new \DateTime('2016-10-12 12:30');
        $begin2 = new \DateTime('2016-10-13 12:00');
        $end2   = new \DateTime('2016-10-13 13:30');
        $begin3 = new \DateTime('2016-10-12 14:00');
        $end3   = new \DateTime('2016-10-12 14:30');
        $mass1  = new Mass($event, $category->reveal(), 'mass1', $begin1, $end1, true, false);
        $mass2  = new Mass($event, $category->reveal(), 'mass2', $begin2, $end2, true, false);
        $mass3  = new Mass($event, $category->reveal(), 'mass3', $begin3, $end3, true, true);
        $assignment = new MassAssignment($mass3, $participant->reveal(), $begin3, $end3);
        $meeting = $this->prophesize(Meeting::class);
        $meeting->getAllParticipants()->willReturn([$participant->reveal()]);
        $happening = $this->prophesize(HappeningParticipation::class);
        $happening->getParticipant()->willReturn($participant->reveal());
        $unavailability1 = $this->prophesize(Unavailability::class);
        $unavailability1->getParticipant()->willReturn($participant->reveal());
        $unavailability2 = $this->prophesize(Unavailability::class);
        $unavailability2->getParticipant()->willReturn($participant->reveal());

        $participant->getId()->willReturn(1234);
        $participant->getSheet()->willReturn($sheet);
        $sheet->getEvent()->willReturn($event);

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day1, $day2]);
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->findByParticipants([$participant->reveal()])->shouldBeCalled()->willReturn([$happening->reveal()]);
        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->findByParticipants([$participant->reveal()])->shouldBeCalled()->willReturn([$unavailability1->reveal(), $unavailability2->reveal()]);

        $massUnavailabilityRepository = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findNotDispatchedByEvent($event)->shouldBeCalled()->willReturn([$mass1, $mass2]);

        $assignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $assignmentRepository->findEnabledByParticipants([$participant->reveal()])->shouldBeCalled()->willReturn([$assignment]);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->findByParticipants([$participant->reveal()])->shouldBeCalled()->willReturn([$meeting->reveal()]);

        $dayViewQueryHandler              = $this->prophesize(DayViewQueryHandler::class);
        $dayView1 = new DayView($beginDay1, $endDay1, [], [], [], [], []);
        $dayView2 = new DayView($beginDay2, $endDay2, [], [], [], [], []);
        $dayViewQueryHandler->handle(
            new DayViewQuery(
                $sheet->reveal(),
                $day1,
                $locale,
                [$unavailability1->reveal(), $unavailability2],
                [$happening->reveal()],
                [$mass1, $mass2],
                [$assignment],
                [$meeting->reveal()]
            )
        )->shouldBeCalled()->willReturn($dayView1);
        $dayViewQueryHandler->handle(
            new DayViewQuery(
                $sheet->reveal(),
                $day2,
                $locale,
                [$unavailability1->reveal(), $unavailability2],
                [$happening->reveal()],
                [$mass1, $mass2],
                [$assignment],
                [$meeting->reveal()]
            )
        )->shouldBeCalled()->willReturn($dayView2);

        $query   = new PlanningViewQuery($event, $participant->reveal(), $locale);
        $handler = new PlanningViewQueryHandler(
            $dayRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $assignmentRepository->reveal(),
            $meetingRepository->reveal(),
            $dayViewQueryHandler->reveal()
        );
        $handler->preloadForParticipants([$participant->reveal()]);
        $result = $handler->handle($query);

        // Expected
        $expected = new PlanningView([$dayView1, $dayView2]);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testHandlePreloadForEvent()
    {
        $event       = EventFactory::createEvent();
        $sheet       = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $locale      = 'fr';
        $beginDay1   = new \DateTime('2016-10-12 10:00');
        $endDay1     = new \DateTime('2016-10-12 18:00');
        $beginDay2   = new \DateTime('2016-10-13 10:00');
        $endDay2     = new \DateTime('2016-10-13 18:00');
        $day1        = new Day($event, $beginDay1, $endDay1);
        $day2        = new Day($event, $beginDay2, $endDay2);

        $category = $this->prophesize(Category::class);
        $begin1 = new \DateTime('2016-10-12 11:00');
        $end1   = new \DateTime('2016-10-12 12:30');
        $begin2 = new \DateTime('2016-10-13 12:00');
        $end2   = new \DateTime('2016-10-13 13:30');
        $begin3 = new \DateTime('2016-10-12 14:00');
        $end3   = new \DateTime('2016-10-12 14:30');
        $mass1  = new Mass($event, $category->reveal(), 'mass1', $begin1, $end1, true, false);
        $mass2 = new Mass($event, $category->reveal(), 'mass2', $begin2, $end2, true, false);
        $mass3 = new Mass($event, $category->reveal(), 'mass3', $begin3, $end3, true, true);
        $assignment = new MassAssignment($mass3, $participant->reveal(), $begin3, $end3);
        $meeting = $this->prophesize(Meeting::class);
        $meeting->getAllParticipants()->willReturn([$participant->reveal()]);
        $happening = $this->prophesize(HappeningParticipation::class);
        $happening->getParticipant()->willReturn($participant->reveal());
        $unavailability1 = $this->prophesize(Unavailability::class);
        $unavailability1->getParticipant()->willReturn($participant->reveal());
        $unavailability2 = $this->prophesize(Unavailability::class);
        $unavailability2->getParticipant()->willReturn($participant->reveal());

        $participant->getId()->willReturn(1234);
        $participant->getSheet()->willReturn($sheet);
        $sheet->getEvent()->willReturn($event);

        // Mock
        $dayRepository                    = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day1, $day2]);
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->getByEvent($event)->shouldBeCalled()->willReturn([$happening->reveal()]);
        $unavailabilityRepository         = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->getByEvent($event)->shouldBeCalled()->willReturn([$unavailability1->reveal(), $unavailability2->reveal()]);

        $massUnavailabilityRepository     = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findNotDispatchedByEvent($event)->shouldBeCalled()->willReturn([$mass1, $mass2]);

        $assignmentRepository             = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $assignmentRepository->findEnabledByEvent($event)->shouldBeCalled()->willReturn([$assignment]);

        $meetingRepository                = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->getAllByEvent($event)->shouldBeCalled()->willReturn([$meeting->reveal()]);

        $dayViewQueryHandler              = $this->prophesize(DayViewQueryHandler::class);
        $dayView1 = new DayView($beginDay1, $endDay1, [], [], [], [], []);
        $dayView2 = new DayView($beginDay2, $endDay2, [], [], [], [], []);
        $dayViewQueryHandler->handle(
            new DayViewQuery(
                $sheet->reveal(),
                $day1,
                $locale,
                [$unavailability1->reveal(), $unavailability2],
                [$happening->reveal()],
                [$mass1, $mass2],
                [$assignment],
                [$meeting->reveal()]
            )
        )->shouldBeCalled()->willReturn($dayView1);
        $dayViewQueryHandler->handle(
            new DayViewQuery(
                $sheet->reveal(),
                $day2,
                $locale,
                [$unavailability1->reveal(), $unavailability2],
                [$happening->reveal()],
                [$mass1, $mass2],
                [$assignment],
                [$meeting->reveal()]
            )
        )->shouldBeCalled()->willReturn($dayView2);

        $query   = new PlanningViewQuery($event, $participant->reveal(), $locale);
        $handler = new PlanningViewQueryHandler(
            $dayRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $assignmentRepository->reveal(),
            $meetingRepository->reveal(),
            $dayViewQueryHandler->reveal()
        );
        $handler->preloadForEvent($event);
        $result = $handler->handle($query);

        // Expected
        $expected = new PlanningView([$dayView1, $dayView2]);

        // Assert
        $this->assertEquals($expected, $result);
    }
}
