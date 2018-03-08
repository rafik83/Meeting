<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Meeting\Slot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailabilityView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class SlotAvailabilityTest extends TestCase
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var MassRepositoryInterface
     */
    private $massUnavailabilityRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /** @var Event */
    private $event;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var User */
    private $user;

    /**
     * Set up the prophecy
     */
    public function setUp()
    {
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->unavailabilityRepository         = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $this->massUnavailabilityRepository     = $this->prophesize(MassRepositoryInterface::class);
        $this->meetingRepository                = $this->prophesize(MeetingRepositoryInterface::class);
        $this->massAssignmentRepository         = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $this->event                            = EventFactory::createEvent();
        $this->sheet                            = SheetFactory::create($this->event);
        $this->user                             = UserFactory::create();
        $this->participant                      = ParticipantFactory::create($this->sheet, $this->user);
    }

    /**
     * Assert SLOT_AVAILABLE
     */
    public function testIsAvailableTrue()
    {
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin = new \DateTime('2016-10-12 09:00:00.000');
        $end   = new \DateTime('2016-10-12 10:00:00.000');
        $slot  = new MeetingSlot($this->event, $begin, $end);

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert SLOT_AVAILABLE
     */
    public function testIsAvailableTrueAsHappeningNotAtSameTime()
    {
        $category               = new Happening\Category($this->event, 'picto', 1, 'leftColor', 'rightColor');
        $beginH                 = new \DateTime('2016-10-12 08:00:00.000');
        $endH                   = new \DateTime('2016-10-12 08:30:00.000');
        $happening              = new Happening($this->event, $beginH, $endH, $category, []);
        $happeningParticipation = new HappeningParticipation($happening, $this->user);

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$happeningParticipation]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin = new \DateTime('2016-10-12 09:00:00.000');
        $end   = new \DateTime('2016-10-12 10:00:00.000');
        $slot  = new MeetingSlot($this->event, $begin, $end);

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert HAPPENING_UNAVAILABILITY
     */
    public function testIsAvailableFalseAsHappeningAtSameTime()
    {
        $category               = new Happening\Category($this->event, 'picto', 1, 'leftColor', 'rightColor');
        $beginH                 = new \DateTime('2016-10-12 09:10:00.000');
        $endH                   = new \DateTime('2016-10-12 10:30:00.000');
        $happening              = new Happening($this->event, $beginH, $endH, $category, []);
        $happeningParticipation = new HappeningParticipation($happening, $this->user);

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$happeningParticipation]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin = new \DateTime('2016-10-12 09:00:00.000');
        $end   = new \DateTime('2016-10-12 10:00:00.000');
        $slot  = new MeetingSlot($this->event, $begin, $end);

        $result = $slotAvailability->getSlotAvailability($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::HAPPENING_UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert HAPPENING_UNAVAILABILITY
     */
    public function testIsAvailableFalseAsHappeningWithHoursOutOfSlot()
    {
        $category               = new Happening\Category($this->event, 'picto', 1, 'leftColor', 'rightColor');
        $beginH                 = new \DateTime('2016-10-12 08:30:00.000');
        $endH                   = new \DateTime('2016-10-12 09:30:00.000');
        $happening              = new Happening($this->event, $beginH, $endH, $category, []);
        $happeningParticipation = new HappeningParticipation($happening, $this->user);

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$happeningParticipation]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin = new \DateTime('2016-10-12 09:00:00.000');
        $end   = new \DateTime('2016-10-12 10:00:00.000');
        $slot  = new MeetingSlot($this->event, $begin, $end);

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::HAPPENING_UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert HAPPENING_UNAVAILABILITY
     */
    public function testIsAvailableFalseAsHappeningWithHoursInTheSlot()
    {
        $category               = new Happening\Category($this->event, 'picto', 1, 'leftColor', 'rightColor');
        $beginH                 = new \DateTime('2016-10-12 09:30:00.000');
        $endH                   = new \DateTime('2016-10-12 09:45:00.000');
        $happening              = new Happening($this->event, $beginH, $endH, $category, []);
        $happeningParticipation = new HappeningParticipation($happening, $this->user);

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$happeningParticipation]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin = new \DateTime('2016-10-12 09:00:00.000');
        $end   = new \DateTime('2016-10-12 10:00:00.000');
        $slot  = new MeetingSlot($this->event, $begin, $end);

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::HAPPENING_UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert SLOT_AVAILABLE
     */
    public function testIsAvailableTrueAsMeetingNotOnTheSlot()
    {
        $beginS        = new \DateTime('2016-10-12 08:00:00.000');
        $endS          = new \DateTime('2016-10-12 09:00:00.000');
        $meetingSlot   = new MeetingSlot($this->event, $beginS, $endS);
        $toSheet       = SheetFactory::create($this->event);
        $toParticipant = ParticipantFactory::create($toSheet);
        $spot          = SpotFactory::create($this->event);
        $user          = UserFactory::create();
        $request       = new Meeting\Request($this->sheet, [], $toSheet, [], new \DateTime(), $user, $this->event);
        $meeting       = new Meeting(
            $request,
            $meetingSlot,
            $this->sheet,
            [$this->participant],
            $toSheet,
            [$toParticipant],
            $beginS,
            $spot,
            $this->event
        );

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([$meeting]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin = new \DateTime('2016-10-12 09:00:00.000');
        $end   = new \DateTime('2016-10-12 10:00:00.000');
        $slot  = new MeetingSlot($this->event, $begin, $end);

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MEETING_UNAVAILABILITY
     */
    public function testIsAvailableFalseAsMeetingOnTheSameSlot()
    {
        $toSheet       = SheetFactory::create($this->event);
        $toParticipant = ParticipantFactory::create($toSheet);
        $begin   = new \DateTime('2016-10-12 09:00:00.000');
        $end     = new \DateTime('2016-10-12 10:00:00.000');
        $slot    = new MeetingSlot($this->event, $begin, $end);
        $spot    = SpotFactory::create($this->event);
        $user          = UserFactory::create();
        $request       = new Meeting\Request($this->sheet, [], $toSheet, [], new \DateTime(), $user, $this->event);
        $meeting = new Meeting(
            $request,
            $slot,
            $this->sheet,
            [$this->participant],
            $toSheet,
            [$toParticipant],
            $begin,
            $spot,
            $this->event
        );

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([$meeting]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MEETING_UNAVAILABILITY
     */
    public function testIsAvailableFalseWithMultipleMeetingToCheckTheGoodOneIsReturned()
    {
        $toSheet       = SheetFactory::create($this->event);
        $toParticipant = ParticipantFactory::create($toSheet);
        $begin         = new \DateTime('2016-10-12 09:00:00.000');
        $end           = new \DateTime('2016-10-12 10:00:00.000');
        $slot          = new MeetingSlot($this->event, $begin, $end);
        $spot          = SpotFactory::create($this->event);
        $user          = UserFactory::create();
        $request       = new Meeting\Request($this->sheet, [], $toSheet, [], new \DateTime(), $user, $this->event);
        $meeting       = new Meeting(
            $request,
            $slot,
            $this->sheet,
            [$this->participant],
            $toSheet,
            [$toParticipant],
            $begin,
            $spot,
            $this->event
        );

        $beginS         = new \DateTime('2016-10-12 08:00:00.000');
        $endS           = new \DateTime('2016-10-12 09:00:00.000');
        $meetingSlot    = new MeetingSlot($this->event, $beginS, $endS);
        $toSheet2       = SheetFactory::create($this->event);
        $toParticipant2 = ParticipantFactory::create($toSheet);
        $spot2          = SpotFactory::create($this->event, 'ref2');
        $request2       = new Meeting\Request($this->sheet, [], $toSheet2, [], new \DateTime(), $user, $this->event);
        $meeting2       = new Meeting(
            $request2,
            $meetingSlot,
            $this->sheet,
            [$this->participant],
            $toSheet2,
            [$toParticipant2],
            $beginS,
            $spot2,
            $this->event
        );

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([$meeting2, $meeting]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert SLOT_AVAILABLE
     */
    public function testIsAvailableTrueAsMassIsNotAtTheSameTime()
    {
        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $beginM   = new \DateTime('2016-10-12 08:00:00.000');
        $endM     = new \DateTime('2016-10-12 08:30:00.000');
        $mass     = new Mass($this->event, $category, 'name', $beginM, $endM, true);
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MASS_UNAVAILABILITY
     */
    public function testIsAvailableFalseAsParticipantHasMassUnavailabilityInsideSlot()
    {
        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $beginM   = new \DateTime('2016-10-12 09:30:00.000');
        $endM     = new \DateTime('2016-10-12 09:45:00.000');
        $mass     = new Mass($this->event, $category, 'name', $beginM, $endM, true);
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MASS_UNAVAILABILITY
     */
    public function testIsAvailableFalseAsParticipantHasMassAssingmentyInsideSlot()
    {
        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $beginM   = new \DateTime('2016-10-12 09:30:00.000');
        $beginMa  = new \DateTime('2016-10-12 09:40:00.000');
        $endM     = new \DateTime('2016-10-12 09:45:00.000');
        $endMa    = new \DateTime('2016-10-12 09:42:00.000');
        $user     = $this->prophesize(User::class);
        $user->getId()->willReturn(123456);
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->willReturn(654321);
        $participant->getUser()->willReturn($user->reveal());
        $participant->getSheet()->willReturn($this->sheet);
        $mass       = new Mass($this->event, $category, 'name', $beginM, $endM, true, true);
        $assignment = new Unavailability\MassAssignment($mass, $user->reveal(), $beginMa, $endMa);
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([$assignment]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $participant->reveal());

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY, null, $assignment);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MASS_UNAVAILABILITY
     */
    public function testIsAvailableTrueAsParticipantHasMassAssingmentyInsideSlotButNotEnabled()
    {
        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $beginM   = new \DateTime('2016-10-12 09:30:00.000');
        $beginMa  = new \DateTime('2016-10-12 09:40:00.000');
        $endM     = new \DateTime('2016-10-12 09:45:00.000');
        $endMa    = new \DateTime('2016-10-12 09:42:00.000');
        $user     = $this->prophesize(User::class);
        $user->getId()->willReturn(123456);
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->willReturn(654321);
        $participant->getUser()->willReturn($user->reveal());
        $participant->getSheet()->willReturn($this->sheet);

        $mass       = new Mass($this->event, $category, 'name', $beginM, $endM, true, true);
        $assignment = new Unavailability\MassAssignment($mass, $user->reveal(), $beginMa, $endMa);
        $assignment->disable();

        // Assertion
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);

        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([$assignment]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $participant->reveal());

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE, null, null);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MASS_UNAVAILABILITY
     */
    public function testIsAvailableFalseAsParticipantHasMassUnavailabilityOutsideSlot()
    {
        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $beginM   = new \DateTime('2016-10-12 08:30:00.000');
        $endM     = new \DateTime('2016-10-12 10:45:00.000');
        $mass     = new Mass($this->event, $category, 'name', $beginM, $endM, true);
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert SLOT_AVAILABLE
     */
    public function testIsAvailableTrueAsUnavailabilityNotAtTheSameTime()
    {
        $beginM         = new \DateTime('2016-10-12 08:30:00.000');
        $endM           = new \DateTime('2016-10-12 08:45:00.000');
        $unavailability = new Unavailability(
            $this->participant->getUser(),
            $this->participant->getSheet()->getEvent(),
            $beginM,
            $endM
        );
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$unavailability]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert UNAVAILABILITY
     */
    public function testIsAvailableFalseAsUnavailabilityAtTheSameTime()
    {
        $beginM         = new \DateTime('2016-10-12 09:30:00.000');
        $endM           = new \DateTime('2016-10-12 09:45:00.000');
        $unavailability = new Unavailability(
            $this->participant->getUser(),
            $this->participant->getSheet()->getEvent(),
            $beginM,
            $endM
        );
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$unavailability]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert UNAVAILABILITY
     */
    public function testIsAvailableFalseAsUnavailabilityAtTheSameTimeWithBeginOut()
    {
        $beginM         = new \DateTime('2016-10-12 08:30:00.000');
        $endM           = new \DateTime('2016-10-12 09:45:00.000');
        $unavailability = new Unavailability(
            $this->participant->getUser(),
            $this->participant->getSheet()->getEvent(),
            $beginM,
            $endM
        );
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$unavailability]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert UNAVAILABILITY
     */
    public function testIsAvailableFalseAsUnavailabilityAtTheSameTimeWithEndOut()
    {
        $beginM         = new \DateTime('2016-10-12 09:30:00.000');
        $endM           = new \DateTime('2016-10-12 10:45:00.000');
        $unavailability = new Unavailability(
            $this->participant->getUser(),
            $this->participant->getSheet()->getEvent(),
            $beginM,
            $endM
        );
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$unavailability]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 10:00:00.000');
        $slot   = new MeetingSlot($this->event, $begin, $end);
        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::UNAVAILABILITY);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MEETING_UNAVAILABILITY
     */
    public function testIsAvailableToCheckOrderOfResultUnavailability()
    {
        $toSheet       = SheetFactory::create($this->event);
        $toParticipant = ParticipantFactory::create($toSheet);
        $begin         = new \DateTime('2016-10-12 09:00:00.000');
        $end           = new \DateTime('2016-10-12 10:00:00.000');
        $slot          = new MeetingSlot($this->event, $begin, $end);
        $spot          = SpotFactory::create($this->event);
        $user          = UserFactory::create();
        $request       = new Meeting\Request($this->sheet, [], $toSheet, [], new \DateTime(), $user, $this->event);
        $meeting       = new Meeting(
            $request,
            $slot,
            $this->sheet,
            [$this->participant],
            $toSheet,
            [$toParticipant],
            $begin,
            $spot,
            $this->event
        );

        $beginM         = new \DateTime('2016-10-12 09:30:00.000');
        $endM           = new \DateTime('2016-10-12 10:45:00.000');
        $unavailability = new Unavailability(
            $this->participant->getUser(),
            $this->participant->getSheet()->getEvent(),
            $beginM,
            $endM
        );
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([$meeting]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$unavailability]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MEETING_UNAVAILABILITY
     */
    public function testIsAvailableToCheckOrderOfResultHappening()
    {
        $toSheet       = SheetFactory::create($this->event);
        $toParticipant = ParticipantFactory::create($toSheet);
        $begin         = new \DateTime('2016-10-12 09:00:00.000');
        $end           = new \DateTime('2016-10-12 10:00:00.000');
        $slot          = new MeetingSlot($this->event, $begin, $end);
        $spot          = SpotFactory::create($this->event);
        $user          = UserFactory::create();
        $request       = new Meeting\Request($this->sheet, [], $toSheet, [], new \DateTime(), $user, $this->event);
        $meeting       = new Meeting(
            $request,
            $slot,
            $this->sheet,
            [$this->participant],
            $toSheet,
            [$toParticipant],
            $begin,
            $spot,
            $this->event
        );

        $category               = new Happening\Category($this->event, 'picto', 1, 'leftColor', 'rightColor');
        $beginH                 = new \DateTime('2016-10-12 09:10:00.000');
        $endH                   = new \DateTime('2016-10-12 10:30:00.000');
        $happening              = new Happening($this->event, $beginH, $endH, $category, []);
        $happeningParticipation = new HappeningParticipation($happening, $this->user);

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$happeningParticipation]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([$meeting]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MEETING_UNAVAILABILITY
     */
    public function testIsAvailableToCheckOrderOfResultMass()
    {
        $toSheet       = SheetFactory::create($this->event);
        $toParticipant = ParticipantFactory::create($toSheet);
        $begin         = new \DateTime('2016-10-12 09:00:00.000');
        $end           = new \DateTime('2016-10-12 10:00:00.000');
        $slot          = new MeetingSlot($this->event, $begin, $end);
        $spot          = SpotFactory::create($this->event);
        $user          = UserFactory::create();
        $request       = new Meeting\Request($this->sheet, [], $toSheet, [], new \DateTime(), $user, $this->event);
        $meeting       = new Meeting(
            $request,
            $slot,
            $this->sheet,
            [$this->participant],
            $toSheet,
            [$toParticipant],
            $begin,
            $spot,
            $this->event
        );

        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $beginM   = new \DateTime('2016-10-12 09:30:00.000');
        $endM     = new \DateTime('2016-10-12 09:45:00.000');
        $mass     = new Mass($this->event, $category, 'name', $beginM, $endM, true);

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([$meeting]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Assert MEETING_UNAVAILABILITY
     */
    public function testIsAvailableToCheckOrderOfResult()
    {
        // In Case of unavailability, happening, mass, and meeting (this case should not happen)
        // The result should be MEETING_UNAVAILABILITY
        $toSheet       = SheetFactory::create($this->event);
        $toParticipant = ParticipantFactory::create($toSheet);
        $begin         = new \DateTime('2016-10-12 09:00:00.000');
        $end           = new \DateTime('2016-10-12 10:00:00.000');
        $slot          = new MeetingSlot($this->event, $begin, $end);
        $spot          = SpotFactory::create($this->event);
        $user          = UserFactory::create();
        $request       = new Meeting\Request($this->sheet, [], $toSheet, [], new \DateTime(), $user, $this->event);
        $meeting       = new Meeting(
            $request,
            $slot,
            $this->sheet,
            [$this->participant],
            $toSheet,
            [$toParticipant],
            $begin,
            $spot,
            $this->event
        );

        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $beginM   = new \DateTime('2016-10-12 09:30:00.000');
        $endM     = new \DateTime('2016-10-12 09:45:00.000');
        $mass     = new Mass($this->event, $category, 'name', $beginM, $endM, true);

        $beginM         = new \DateTime('2016-10-12 09:30:00.000');
        $endM           = new \DateTime('2016-10-12 10:45:00.000');
        $unavailability = new Unavailability(
            $this->participant->getUser(),
            $this->participant->getSheet()->getEvent(),
            $beginM,
            $endM
        );

        $category               = new Happening\Category($this->event, 'picto', 1, 'leftColor', 'rightColor');
        $beginH                 = new \DateTime('2016-10-12 09:10:00.000');
        $endH                   = new \DateTime('2016-10-12 10:30:00.000');
        $happening              = new Happening($this->event, $beginH, $endH, $category, []);
        $happeningParticipation = new HappeningParticipation($happening, $this->user);

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$happeningParticipation]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([$meeting]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$unavailability]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }
}
