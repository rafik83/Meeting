<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Unavailability;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Domain\Unavailability\TimeSlotDispatcher;

class TimeSlotDispatcherTest extends TestCase
{
    public function testDispatchNotEnabledThrowingUnableToDispatchException()
    {
        $this->expectException(UnableToDispatchException::class);

        // Dispatch not enabled
        $mass = $this->createMass(new \DateTime('2017-01-31 12:00:00'), new \DateTime('2017-01-31 14:00:00'), false, []);

        $userRepository           = $this->prophesize(UserRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $jobQueueAdapter          = $this->prophesize(JobQueueInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $userRepository->reveal(),
            $massRepository->reveal(),
            $massAssignmentRepository->reveal(),
            $jobQueueAdapter->reveal()
        );

        $dispatcher->dispatch($mass);
    }

    public function testNoTimeSlotsThrowingUnableToDispatchException()
    {
        $this->expectException(UnableToDispatchException::class);

        // Dispatch enabled but not time slots
        $mass = $this->createMass(new \DateTime('2017-01-31 12:00:00'), new \DateTime('2017-01-31 14:00:00'), true, []);

        $userRepository           = $this->prophesize(UserRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $jobQueueAdapter          = $this->prophesize(JobQueueInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $userRepository->reveal(),
            $massRepository->reveal(),
            $massAssignmentRepository->reveal(),
            $jobQueueAdapter->reveal()
        );

        $dispatcher->dispatch($mass);
    }

    public function testDispatch()
    {
        $begin     = new \DateTime('2017-01-31 12:00:00');
        $end       = new \DateTime('2017-01-31 14:00:00');
        $timeSlots = [
            ['from' => new \DateTime('2017-01-31 12:00:00'), 'to' => new \DateTime('2017-01-31 13:00:00')],
            ['from' => new \DateTime('2017-01-31 13:00:00'), 'to' => new \DateTime('2017-01-31 14:00:00')],
        ];

        // Dispatch enabled and time slots provided
        $mass = $this->createMass($begin, $end, true, $timeSlots);

        $userRepository           = $this->prophesize(UserRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $jobQueueAdapter          = $this->prophesize(JobQueueInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $userRepository->reveal(),
            $massRepository->reveal(),
            $massAssignmentRepository->reveal(),
            $jobQueueAdapter->reveal()
        );

        $user1 = $this->prophesize(User::class);
        $user1->getId()->willReturn(1);
        $user2 = $this->prophesize(User::class);
        $user2->getId()->willReturn(2);
        $user3 = $this->prophesize(User::class);
        $user3->getId()->willReturn(3);
        $user4 = $this->prophesize(User::class);
        $user4->getId()->willReturn(4);

        $users = [
            $user1->reveal(),
            $user2->reveal(),
            $user3->reveal(),
            $user4->reveal(),
        ];

        $userRepository->findByEventWithoutDispatch($mass->getEvent(), $mass)->shouldBeCalled()
            ->willReturn($users);
        $massAssignmentRepository->add(new MassAssignment($mass, $user1->reveal(), $timeSlots[0]['from'], $timeSlots[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($mass, $user2->reveal(), $timeSlots[1]['from'], $timeSlots[1]['to']));
        $massAssignmentRepository->add(new MassAssignment($mass, $user3->reveal(), $timeSlots[0]['from'], $timeSlots[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($mass, $user4->reveal(), $timeSlots[1]['from'], $timeSlots[1]['to']));

        $dispatcher->dispatch($mass);
    }

    public function testDispatchAll()
    {
        // Input
        $event = $this->createEvent();

        $timeSlots1 = [
            ['from' => new \DateTime('2017-01-31 12:00:00'), 'to' => new \DateTime('2017-01-31 13:00:00')],
            ['from' => new \DateTime('2017-01-31 13:00:00'), 'to' => new \DateTime('2017-01-31 14:00:00')],
        ];
        $timeSlots2 = [
            ['from' => new \DateTime('2017-01-30 16:00:00'), 'to' => new \DateTime('2017-01-30 17:00:00')],
            ['from' => new \DateTime('2017-01-30 17:00:00'), 'to' => new \DateTime('2017-01-30 18:00:00')],
            ['from' => new \DateTime('2017-01-30 18:00:00'), 'to' => new \DateTime('2017-01-30 19:00:00')],
        ];

        $masses = [
            $this->createMass(new \DateTime('2017-01-31 12:00:00'), new \DateTime('2017-01-31 14:00:00'), true, $timeSlots1, $event),
            $this->createMass(new \DateTime('2017-01-30 16:00:00'), new \DateTime('2017-01-30 19:00:00'), true, $timeSlots2, $event),
        ];

        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1);
        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(2);
        $user3 = $this->prophesize(User::class);
        $user3->getId()->shouldBeCalled()->willReturn(3);
        $user4 = $this->prophesize(User::class);
        $user4->getId()->shouldBeCalled()->willReturn(4);

        $users = [
            $user1->reveal(),
            $user2->reveal(),
            $user3->reveal(),
            $user4->reveal(),
        ];

        $jobQueueAdapter = $this->prophesize(JobQueueInterface::class);

        $jobQueueAdapter->aggregateUsersFullUnavailability(
            $event,
            [
                1 => $user1->reveal(),
                2 => $user2->reveal(),
                3 => $user3->reveal(),
                4 => $user4->reveal(),
            ]
        )->shouldBeCalled();

        // Mock
        $userRepository           = $this->prophesize(UserRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $userRepository->reveal(),
            $massRepository->reveal(),
            $massAssignmentRepository->reveal(),
            $jobQueueAdapter->reveal()
        );

        // Assets
        $massRepository->findDispatchByEvent($event)->shouldBeCalled()->willReturn($masses);

        $userRepository->findByEventWithoutDispatch($event, $masses[0])->shouldBeCalled()
            ->willReturn($users);
        $massAssignmentRepository->add(new MassAssignment($masses[0], $user1->reveal(), $timeSlots1[0]['from'], $timeSlots1[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[0], $user2->reveal(), $timeSlots1[1]['from'], $timeSlots1[1]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[0], $user3->reveal(), $timeSlots1[0]['from'], $timeSlots1[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[0], $user4->reveal(), $timeSlots1[1]['from'], $timeSlots1[1]['to']));

        $userRepository->findByEventWithoutDispatch($event, $masses[1])->shouldBeCalled()
            ->willReturn($users);
        $massAssignmentRepository->add(new MassAssignment($masses[1], $user1->reveal(), $timeSlots2[0]['from'], $timeSlots2[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[1], $user2->reveal(), $timeSlots2[1]['from'], $timeSlots2[1]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[1], $user3->reveal(), $timeSlots2[2]['from'], $timeSlots2[2]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[1], $user4->reveal(), $timeSlots2[0]['from'], $timeSlots2[0]['to']));

        // Run
        $dispatcher->dispatchAll($event);
    }

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $dispatch
     * @param array              $timeSlots
     * @param Event|null         $event
     *
     * @return Mass
     */
    private function createMass(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $dispatch,
        array $timeSlots,
        Event $event = null
    ) {
        $event    = $event ?: $this->createEvent();
        $category = new Category($event, '', '', '', '');

        return new Mass($event, $category, '', $begin, $end, true, $dispatch, $timeSlots);
    }

    /**
     * @return Event
     */
    private function createEvent()
    {
        $prefix = new Prefix('Vimeet', 'Vi');

        return new Event('event', 'fr', ['fr'], Event::VAT_MODE_ATI, 20.0, 'fr', 'EUR', 'Europe\Paris', '', '', '', $prefix);
    }
}
