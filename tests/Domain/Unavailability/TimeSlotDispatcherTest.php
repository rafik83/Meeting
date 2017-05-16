<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Unavailability;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Domain\Unavailability\TimeSlotDispatcher;

class TimeSlotDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchNotEnabledThrowingUnableToDispatchException()
    {
        $this->expectException(UnableToDispatchException::class);

        // Dispatch not enabled
        $mass = $this->createMass(new \DateTime('2017-01-31 12:00:00'), new \DateTime('2017-01-31 14:00:00'), false, []);

        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $jobQueueAdapter          = $this->prophesize(JobQueueInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $participantRepository->reveal(),
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

        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $jobQueueAdapter          = $this->prophesize(JobQueueInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $participantRepository->reveal(),
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

        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $jobQueueAdapter          = $this->prophesize(JobQueueInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $participantRepository->reveal(),
            $massRepository->reveal(),
            $massAssignmentRepository->reveal(),
            $jobQueueAdapter->reveal()
        );

        $participants = [
            $this->createParticipant($mass->getEvent(), 'foobar0@test.com', 1),
            $this->createParticipant($mass->getEvent(), 'foobar1@test.com', 2),
            $this->createParticipant($mass->getEvent(), 'foobar2@test.com', 3),
            $this->createParticipant($mass->getEvent(), 'foobar3@test.com', 4),
        ];

        $participantRepository->findByEventWithoutDispatch($mass->getEvent(), $mass)->shouldBeCalled()
            ->willReturn($participants);
        $massAssignmentRepository->add(new MassAssignment($mass, $participants[0], $timeSlots[0]['from'], $timeSlots[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($mass, $participants[1], $timeSlots[1]['from'], $timeSlots[1]['to']));
        $massAssignmentRepository->add(new MassAssignment($mass, $participants[2], $timeSlots[0]['from'], $timeSlots[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($mass, $participants[3], $timeSlots[1]['from'], $timeSlots[1]['to']));

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
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);
        $user4 = $this->prophesize(User::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);
        $participant4 = $this->prophesize(Participant::class);

        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $participant2->getUser()->shouldBeCalled()->willReturn($user2->reveal());
        $participant3->getUser()->shouldBeCalled()->willReturn($user3->reveal());
        $participant4->getUser()->shouldBeCalled()->willReturn($user4->reveal());

        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
            $participant3->reveal(),
            $participant4->reveal(),
        ];

        $user1->getId()->shouldBeCalled()->willReturn(1);
        $user2->getId()->shouldBeCalled()->willReturn(2);
        $user3->getId()->shouldBeCalled()->willReturn(3);
        $user4->getId()->shouldBeCalled()->willReturn(4);

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
        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $massRepository           = $this->prophesize(MassRepositoryInterface::class);
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);

        $dispatcher = new TimeSlotDispatcher(
            $participantRepository->reveal(),
            $massRepository->reveal(),
            $massAssignmentRepository->reveal(),
            $jobQueueAdapter->reveal()
        );

        // Assets
        $massRepository->findDispatchByEvent($event)->shouldBeCalled()->willReturn($masses);

        $participantRepository->findByEventWithoutDispatch($event, $masses[0])->shouldBeCalled()
            ->willReturn($participants);
        $massAssignmentRepository->add(new MassAssignment($masses[0], $participants[0], $timeSlots1[0]['from'], $timeSlots1[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[0], $participants[1], $timeSlots1[1]['from'], $timeSlots1[1]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[0], $participants[2], $timeSlots1[0]['from'], $timeSlots1[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[0], $participants[3], $timeSlots1[1]['from'], $timeSlots1[1]['to']));

        $participantRepository->findByEventWithoutDispatch($event, $masses[1])->shouldBeCalled()
            ->willReturn($participants);
        $massAssignmentRepository->add(new MassAssignment($masses[1], $participants[0], $timeSlots2[0]['from'], $timeSlots2[0]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[1], $participants[1], $timeSlots2[1]['from'], $timeSlots2[1]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[1], $participants[2], $timeSlots2[2]['from'], $timeSlots2[2]['to']));
        $massAssignmentRepository->add(new MassAssignment($masses[1], $participants[3], $timeSlots2[0]['from'], $timeSlots2[0]['to']));

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
     * @param Event  $event
     * @param string $email
     *
     * @return Participant
     */
    private function createParticipant(Event $event, $email, $id)
    {
        $user  = new User($email, '', '', 'fr');
        $sheet = new Sheet($event, new Type($event), [], $user, new \DateTime());

        $reflection = new \ReflectionClass(Participant::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);

        $participant = new Participant($sheet, $user, [], true);

        $property->setValue($participant, $id);
        $property->setAccessible(false);

        return $participant;
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
