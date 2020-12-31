<?php

namespace Proximum\Vimeet\Tests\Domain\Meeting\Slot;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailabilityView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\GetParticipantTypes;
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
    /** @var ObjectProphecy */
    private $happeningParticipationRepository;

    /** @var ObjectProphecy */
    private $unavailabilityRepository;

    /** @var ObjectProphecy */
    private $massUnavailabilityRepository;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $massAssignmentRepository;

    /** @var ObjectProphecy */
    private $getParticipantTypes;

    /** @var Event */
    private $event;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var User */
    private $user;

    public function setUp()
    {
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $this->massUnavailabilityRepository = $this->prophesize(MassRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $this->getParticipantTypes = $this->prophesize(GetParticipantTypes::class);
        $this->event = EventFactory::createEvent();
        $this->user = UserFactory::create();
        $this->sheet = SheetFactory::create($this->event);
        $this->participant = ParticipantFactory::create($this->sheet, $this->user);
    }

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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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

    public function testIsAvailableFalseAsParticipantHasMassUnavailabilityInsideSlot()
    {
        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $mass = new Mass(
            $this->event,
            $category,
            'name',
            new \DateTime('2016-10-12 09:30:00.000'),
            new \DateTime('2016-10-12 09:45:00.000'),
            true,
            false,
            [],
            [$this->sheet->getType()]
        );
        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->getParticipantTypes->handle($this->participant)->shouldBeCalled()->willReturn([$this->sheet->getType()]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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

        $mass = new Mass($this->event, $category, 'name', $beginM, $endM, true, true, [], [$this->sheet->getType()]);
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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

        $mass = new Mass($this->event, $category, 'name', $beginM, $endM, true, true, [], [$this->sheet->getType()]);
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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

    public function testIsAvailableFalseAsParticipantHasMassUnavailabilityOutsideSlot()
    {
        $category = new Category($this->event, 'picto', 'title', 'leftColor', 'rightColor');
        $mass = new Mass(
            $this->event,
            $category,
            'name',
            new \DateTime('2016-10-12 08:30:00.000'),
            new \DateTime('2016-10-12 10:45:00.000'),
            true,
            false,
            [],
            [$this->sheet->getType()]
        );

        $this->happeningParticipationRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->massUnavailabilityRepository->findBlockingByEvent($this->event)->shouldBeCalled()->willReturn([$mass]);
        $this->massAssignmentRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);
        $this->getParticipantTypes->handle($this->participant)->shouldBeCalled()->willReturn([$this->sheet->getType()]);

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

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
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $result = $slotAvailability->isAvailable($slot, $this->participant);

        // Expected
        $expected = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testIsUsable()
    {
        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1337);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(99);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(936);

        $event = $this->prophesize(Event::class);

        $type1 = $this->prophesize(Type::class);
        $type1->getId()->willReturn(42);

        $type2 = $this->prophesize(Type::class);
        $type2->getId()->willReturn(1848);

        $type3 = $this->prophesize(Type::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;

        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot1->getEvent()->willReturn($event->reveal());
        $slot1->getBegin()->willReturn(new \DateTime('2018-10-15 09:00:00'));
        $slot1->getEnd()->willReturn(new \DateTime('2018-10-15 10:00:00'));

        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot2->getEvent()->willReturn($event->reveal());
        $slot2->getBegin()->willReturn(new \DateTime('2018-10-15 09:30:00'));
        $slot2->getEnd()->willReturn(new \DateTime('2018-10-15 10:20:00'));

        $slot3 = $this->prophesize(MeetingSlot::class);
        $slot3->getEvent()->willReturn($event->reveal());
        $slot3->getBegin()->willReturn(new \DateTime('2018-10-15 11:20:00'));
        $slot3->getEnd()->willReturn(new \DateTime('2018-10-15 11:30:00'));

        $slot4 = $this->prophesize(MeetingSlot::class);
        $slot4->getEvent()->willReturn($event->reveal());
        $slot4->getBegin()->willReturn(new \DateTime('2018-10-15 14:10:00'));
        $slot4->getEnd()->willReturn(new \DateTime('2018-10-15 14:25:00'));

        $slot5 = $this->prophesize(MeetingSlot::class);
        $slot5->getEvent()->willReturn($event->reveal());
        $slot5->getBegin()->willReturn(new \DateTime('2018-10-15 14:25:00'));
        $slot5->getEnd()->willReturn(new \DateTime('2018-10-15 14:45:00'));

        $category = $this->prophesize(Category::class);
        $mass1 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 1',
            new \DateTime('2018-10-15 10:00:00'),
            new \DateTime('2018-10-15 11:00:00'),
            true,
            false,
            [],
            [$type1->reveal()]
        );
        $mass2 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 2',
            new \DateTime('2018-10-15 09:00:00'),
            new \DateTime('2018-10-15 11:00:00'),
            false,
            false,
            [],
            [$type1->reveal()]
        );
        $mass3 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 3',
            new \DateTime('2018-10-15 09:00:00'),
            new \DateTime('2018-10-15 10:00:00'),
            true,
            false,
            [],
            [$type3->reveal()]
        );
        $mass4 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 4',
            new \DateTime('2018-10-15 11:00:00'),
            new \DateTime('2018-10-15 12:00:00'),
            true,
            false,
            [],
            [$type2->reveal()]
        );
        $mass5 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 5',
            new \DateTime('2018-10-15 11:00:00'),
            new \DateTime('2018-10-15 12:00:00'),
            false, // not blocking
            false,
            [],
            [$type1->reveal(), $type2->reveal()]
        );
        $mass6 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 5',
            new \DateTime('2018-10-15 14:00:00'),
            new \DateTime('2018-10-15 15:00:00'),
            true,
            true, // dispatched
            [
                ['from' => new \DateTime('2018-10-15 14:00:00'), 'to' => new \DateTime('2018-10-15 14:30:00')],
                ['from' => new \DateTime('2018-10-15 14:30:00'), 'to' => new \DateTime('2018-10-15 15:00:00')],
            ],
            [$type2->reveal()]
        );

        $this
            ->massUnavailabilityRepository
            ->findBlockingByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$mass1, $mass2, $mass3, $mass4, $mass5, $mass6])
        ;

        $this->happeningParticipationRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($event->reveal())->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn([]);

        $massAssignmentForUser1 = $this->prophesize(Unavailability\MassAssignment::class);
        $massAssignmentForUser1->getUser()->shouldBeCalled()->willReturn($user1->reveal());

        $this
            ->massAssignmentRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$massAssignmentForUser1->reveal()])
        ;

        $this
            ->getParticipantTypes
            ->handle($participant1->reveal())
            ->shouldBeCalled()
            ->willReturn([$type1->reveal(), $type2->reveal()])
        ;
        $this
            ->getParticipantTypes
            ->handle($participant2->reveal())
            ->shouldBeCalled()
            ->willReturn([$type1->reveal()])
        ;

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $this->assertTrue($slotAvailability->isUsable($sheet->reveal(), $slot1->reveal()));
        $this->assertFalse($slotAvailability->isUsable($sheet->reveal(), $slot2->reveal()));
        $this->assertTrue($slotAvailability->isUsable($sheet->reveal(), $slot3->reveal()));
        $this->assertTrue($slotAvailability->isUsable($sheet->reveal(), $slot4->reveal()));
        $this->assertTrue($slotAvailability->isUsable($sheet->reveal(), $slot5->reveal()));
    }

    public function testGetSlotAvailability()
    {
        $event = $this->prophesize(Event::class);

        $user1 = $this->prophesize(User::class);
        //$user1->getId()->shouldBeCalled()->willReturn(1337);

        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(1999);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(99);
        $participant1->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(936);
        $participant2->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $participant2->getUser()->shouldBeCalled()->willReturn($user2->reveal());

        $type1 = $this->prophesize(Type::class);
        $type1->getId()->willReturn(42);

        $type2 = $this->prophesize(Type::class);
        $type2->getId()->willReturn(1848);

        $type3 = $this->prophesize(Type::class);

        $slot1 = new MeetingSlot(
            $event->reveal(),
            new \DateTime('2018-10-15 09:00:00'),
            new \DateTime('2018-10-15 10:00:00')
        );
        $slot2 = new MeetingSlot(
            $event->reveal(),
            new \DateTime('2018-10-15 09:30:00'),
            new \DateTime('2018-10-15 10:20:00')
        );
        $slot3 = new MeetingSlot(
            $event->reveal(),
            new \DateTime('2018-10-15 11:20:00'),
            new \DateTime('2018-10-15 11:30:00')
        );
        $slot4 = new MeetingSlot(
            $event->reveal(),
            new \DateTime('2018-10-15 14:10:00'),
            new \DateTime('2018-10-15 14:25:00')
        );
        $slot5 = new MeetingSlot(
            $event->reveal(),
            new \DateTime('2018-10-15 14:25:00'),
            new \DateTime('2018-10-15 14:45:00')
        );
        $slot6 = new MeetingSlot(
            $event->reveal(),
            new \DateTime('2018-10-15 15:10:00'),
            new \DateTime('2018-10-15 15:50:00')
        );

        $category = $this->prophesize(Category::class);
        $mass1 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 1',
            new \DateTime('2018-10-15 10:00:00'),
            new \DateTime('2018-10-15 11:00:00'),
            true,
            false,
            [],
            [$type1->reveal()]
        );
        $mass2 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 2',
            new \DateTime('2018-10-15 09:00:00'),
            new \DateTime('2018-10-15 11:00:00'),
            false,
            false,
            [],
            [$type1->reveal()]
        );
        $mass3 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 3',
            new \DateTime('2018-10-15 09:00:00'),
            new \DateTime('2018-10-15 10:00:00'),
            true,
            false,
            [],
            [$type3->reveal()]
        );
        $mass4 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 4',
            new \DateTime('2018-10-15 11:00:00'),
            new \DateTime('2018-10-15 12:00:00'),
            true,
            false,
            [],
            [$type2->reveal()]
        );
        $mass5 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 5',
            new \DateTime('2018-10-15 11:00:00'),
            new \DateTime('2018-10-15 12:00:00'),
            false, // not blocking
            false,
            [],
            [$type1->reveal(), $type2->reveal()]
        );
        $mass6 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 5',
            new \DateTime('2018-10-15 14:00:00'),
            new \DateTime('2018-10-15 15:00:00'),
            true,
            true, // dispatched
            [
                ['from' => new \DateTime('2018-10-15 14:00:00'), 'to' => new \DateTime('2018-10-15 14:30:00')],
                ['from' => new \DateTime('2018-10-15 14:30:00'), 'to' => new \DateTime('2018-10-15 15:00:00')],
            ],
            [$type2->reveal()]
        );
        $mass7 = new Mass(
            $event->reveal(),
            $category->reveal(),
            'Mass 5',
            new \DateTime('2018-10-15 15:00:00'),
            new \DateTime('2018-10-15 15:30:00'),
            true,
            false,
            [],
            [$type2->reveal()]
        );

        $this
            ->massUnavailabilityRepository
            ->findBlockingByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$mass1, $mass2, $mass3, $mass4, $mass5, $mass6, $mass7])
        ;

        $this->happeningParticipationRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn([]);
        $this->meetingRepository->getAllByEvent($event->reveal())->shouldBeCalled()->willReturn([]);
        $this->unavailabilityRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn([]);

        $massAssignmentForUser1 = new Unavailability\MassAssignment(
            $mass6,
            $user1->reveal(),
            new \DateTime('2018-10-15 14:30:00'),
            new \DateTime('2018-10-15 15:00:00')
        );

        $this
            ->massAssignmentRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$massAssignmentForUser1])
        ;

        $this
            ->getParticipantTypes
            ->handle($participant1->reveal())
            ->shouldBeCalled()
            ->willReturn([$type1->reveal(), $type2->reveal()])
        ;
        $this
            ->getParticipantTypes
            ->handle($participant2->reveal())
            ->shouldBeCalled()
            ->willReturn([$type1->reveal()])
        ;

        $slotAvailability = new SlotAvailability(
            $this->happeningParticipationRepository->reveal(),
            $this->unavailabilityRepository->reveal(),
            $this->massUnavailabilityRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->massAssignmentRepository->reveal(),
            $this->getParticipantTypes->reveal()
        );

        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY),
            $slotAvailability->getSlotAvailability($slot1, $participant1->reveal())
        );
        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY),
            $slotAvailability->getSlotAvailability($slot1, $participant2->reveal())
        );

        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY),
            $slotAvailability->getSlotAvailability($slot2, $participant1->reveal())
        );
        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY),
            $slotAvailability->getSlotAvailability($slot2, $participant2->reveal())
        );

        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY),
            $slotAvailability->getSlotAvailability($slot3, $participant1->reveal())
        );
        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY),
            $slotAvailability->getSlotAvailability($slot3, $participant2->reveal())
        );

        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE),
            $slotAvailability->getSlotAvailability($slot4, $participant1->reveal())
        );
        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE),
            $slotAvailability->getSlotAvailability($slot4, $participant2->reveal())
        );

        $this->assertEquals(
            new SlotAvailabilityView(
                SlotAvailability::MASS_UNAVAILABILITY,
                null,
                $massAssignmentForUser1
            ),
            $slotAvailability->getSlotAvailability($slot5, $participant1->reveal())
        );
        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE),
            $slotAvailability->getSlotAvailability($slot5, $participant2->reveal())
        );

        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY),
            $slotAvailability->getSlotAvailability($slot6, $participant1->reveal())
        );
        $this->assertEquals(
            new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE),
            $slotAvailability->getSlotAvailability($slot6, $participant2->reveal())
        );
    }
}
