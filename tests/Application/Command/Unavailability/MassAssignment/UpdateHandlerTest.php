<?php

namespace Application\Command\Unavailability\MassAssignment;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Unavailability\MassAssignment\Update;
use Proximum\Vimeet\Application\Command\Unavailability\MassAssignment\UpdateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Mass\Assignment\AssignmentUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOnMeetingException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOutOfMassSlotException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UpdateHandlerTest extends TestCase
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /** @var Category */
    public $category;

    /** @var \DateTime */
    public $massBegin;

    /** @var \DateTime */
    public $massEnd;

    /** @var Mass */
    public $mass;

    public function setUp()
    {
        $this->event    = EventFactory::createEvent();
        $this->user     = UserFactory::create();
        $this->sheet    = SheetFactory::create($this->event, $this->user);
        $this->category = new Category($this->event, '', 'conference', '#000', '#fff');

        $this->massBegin = new \DateTime('2016-01-01 12:00:00');
        $this->massEnd   = new \DateTime('2016-01-01 14:00:00');
        $this->mass      = new Mass($this->event, $this->category, 'conf', $this->massBegin, $this->massEnd, true);
    }

    public function testDisableMassAssignment()
    {
        $assignmentBegin = new \DateTime('2016-01-01 12:15:00');
        $assignmentEnd   = new \DateTime('2016-01-01 12:45:00');
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $participant1->getUser()->willReturn($user->reveal());
        $participant2->getUser()->willReturn($user->reveal());

        $massAssignment = new MassAssignment($this->mass, $user->reveal(), $assignmentBegin, $assignmentEnd);

        // Mock
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $eventDispatcher          = $this->prophesize(DelayedEventDispatcher::class);
        $participantRepository->getAllParticipantForUser($this->mass->getEvent(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()]);

        $eventDispatcher->dispatch(
            Events::MASS_ASSIGNMENT_UPDATED,
            new AssignmentUpdatedEvent($participant1->reveal())
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            Events::MASS_ASSIGNMENT_UPDATED,
            new AssignmentUpdatedEvent($participant2->reveal())
        )->shouldBeCalled();

        $massAssignmentRepository->set($massAssignment)->shouldBeCalled();

        $command          = new Update($massAssignment);
        $command->enabled = false;

        $handler = new UpdateHandler(
            $massAssignmentRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testUpdateDateMassAssignment()
    {
        $assignmentBegin = new \DateTime('2016-01-01 12:15:00');
        $assignmentEnd   = new \DateTime('2016-01-01 12:45:00');
        $participant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $participant->getUser()->willReturn($user->reveal());

        $newBegin = new \DateTime('2016-01-01 13:00:00');
        $newEnd   = new \DateTime('2016-01-01 13:30:00');

        $massAssignment = new MassAssignment($this->mass, $user->reveal(), $assignmentBegin, $assignmentEnd);

        // Mock
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $eventDispatcher          = $this->prophesize(DelayedEventDispatcher::class);
        $participantRepository->getAllParticipantForUser($this->mass->getEvent(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant->reveal()]);

        $eventDispatcher->dispatch(
            Events::MASS_ASSIGNMENT_UPDATED,
            new AssignmentUpdatedEvent($participant->reveal())
        )->shouldBeCalled();

        $participantRepository->getAvailableParticipants(
            [$participant->reveal()],
            $newBegin,
            $newEnd
        )->shouldBeCalled()->willReturn([$participant->reveal()]);

        $massAssignmentRepository->set($massAssignment)->shouldBeCalled();

        $command          = new Update($massAssignment);
        $command->enabled = true;
        $command->begin   = $newBegin;
        $command->end     = $newEnd;

        $handler = new UpdateHandler(
            $massAssignmentRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testMassAssignmentOutOfMass()
    {
        $this->expectException(MassAssignmentOutOfMassSlotException::class);

        $assignmentBegin = new \DateTime('2016-01-01 12:15:00');
        $assignmentEnd   = new \DateTime('2016-01-01 12:45:00');
        $participant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $participant->getUser()->willReturn($user->reveal());

        $newBegin = new \DateTime('2016-01-01 11:00:00');
        $newEnd   = new \DateTime('2016-01-01 15:30:00');

        $massAssignment = new MassAssignment($this->mass, $user->reveal(), $assignmentBegin, $assignmentEnd);

        // Mock
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $eventDispatcher          = $this->prophesize(DelayedEventDispatcher::class);
        $participantRepository->getAllParticipantForUser($this->mass->getEvent(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant->reveal()]);

        $eventDispatcher->dispatch(
            Events::MASS_ASSIGNMENT_UPDATED,
            new AssignmentUpdatedEvent($participant->reveal())
        )->shouldNotBeCalled();

        $participantRepository->getAvailableParticipants(
            [$participant->reveal()],
            $newBegin,
            $newEnd
        )->shouldBeCalled()->willReturn([$participant]); // hasMeetingOrHappening

        $command          = new Update($massAssignment);
        $command->enabled = true;
        $command->begin   = $newBegin;
        $command->end     = $newEnd;

        $handler = new UpdateHandler(
            $massAssignmentRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testMassAssignmentOnMeeting()
    {
        $this->expectException(MassAssignmentOnMeetingException::class);

        $assignmentBegin = new \DateTime('2016-01-01 12:15:00');
        $assignmentEnd   = new \DateTime('2016-01-01 12:45:00');
        $participant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $participant->getUser()->willReturn($user->reveal());

        $newBegin = new \DateTime('2016-01-01 11:00:00');
        $newEnd   = new \DateTime('2016-01-01 15:30:00');

        $massAssignment = new MassAssignment($this->mass, $user->reveal(), $assignmentBegin, $assignmentEnd);

        // Mock
        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $eventDispatcher          = $this->prophesize(DelayedEventDispatcher::class);
        $participantRepository->getAllParticipantForUser($this->mass->getEvent(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant->reveal()]);

        $eventDispatcher->dispatch(
            Events::MASS_ASSIGNMENT_UPDATED,
            new AssignmentUpdatedEvent($participant->reveal())
        )->shouldNotBeCalled();

        $participantRepository->getAvailableParticipants(
            [$participant->reveal()],
            $newBegin,
            $newEnd
        )->shouldBeCalled()->willReturn([]);

        $command          = new Update($massAssignment);
        $command->enabled = true;
        $command->begin   = $newBegin;
        $command->end     = $newEnd;

        $handler = new UpdateHandler(
            $massAssignmentRepository->reveal(),
            $participantRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }
}
