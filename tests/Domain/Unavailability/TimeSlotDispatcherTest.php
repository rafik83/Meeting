<?php

namespace Proximum\Vimeet\Tests\Domain\Unavailability;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\Unavailability\MassTimeSlot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Domain\Unavailability\Mass\IsMassUnavailabilityAssignedToAllTypes;
use Proximum\Vimeet\Domain\Unavailability\TimeSlotDispatcher;

class TimeSlotDispatcherTest extends TestCase
{
    private $userRepository;
    private $massRepository;
    private $massAssignmentRepository;
    private $typeRepository;
    private $isMassUnavailabilityAssignedToAllTypes;
    private $jobQueueAdapter;
    private $dispatcher;
    private $event;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->massRepository = $this->prophesize(MassRepositoryInterface::class);
        $this->massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $this->jobQueueAdapter = $this->prophesize(JobQueueInterface::class);
        $this->isMassUnavailabilityAssignedToAllTypes = $this->prophesize(IsMassUnavailabilityAssignedToAllTypes::class);

        $this->dispatcher = new TimeSlotDispatcher(
            $this->userRepository->reveal(),
            $this->massRepository->reveal(),
            $this->massAssignmentRepository->reveal(),
            $this->typeRepository->reveal(),
            $this->isMassUnavailabilityAssignedToAllTypes->reveal(),
            $this->jobQueueAdapter->reveal()
        );
    }

    public function testDispatchNotEnabledThrowingUnableToDispatchException()
    {
        $this->expectException(UnableToDispatchException::class);

        $mass = $this->prophesize(Mass::class);
        $mass->isDispatch()->shouldBeCalled()->willReturn(false);

        $this
            ->massRepository
            ->findDispatchByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$mass->reveal()])
        ;

        $this->dispatcher->dispatchAll($this->event->reveal());
    }

    public function testNoTimeSlotsThrowingUnableToDispatchException()
    {
        $this->expectException(UnableToDispatchException::class);

        $mass = $this->prophesize(Mass::class);
        $mass->isDispatch()->shouldBeCalled()->willReturn(true);
        $mass->getTimeSlots()->shouldBeCalled()->willReturn([]);

        $this
            ->massRepository
            ->findDispatchByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$mass->reveal()])
        ;

        $this->dispatcher->dispatchAll($this->event->reveal());
    }

    public function testDispatch()
    {
        $mass1 = $this->prophesize(Mass::class);
        $mass1->isDispatch()->shouldBeCalled()->willReturn(true);
        $mass1
            ->getTimeSlots()
            ->shouldBeCalled()
            ->willReturn(
                [
                    new MassTimeSlot(
                        $mass1->reveal(),
                        new \DateTime('2017-01-31 12:00:00'),
                        new \DateTime('2017-01-31 13:00:00')
                    ),
                    new MassTimeSlot(
                        $mass1->reveal(),
                        new \DateTime('2017-01-31 13:00:00'),
                        new \DateTime('2017-01-31 14:00:00')
                    ),
                ]
            )
        ;
        $mass1->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());

        $mass2 = $this->prophesize(Mass::class);
        $mass2->isDispatch()->shouldBeCalled()->willReturn(true);
        $mass2
            ->getTimeSlots()
            ->shouldBeCalled()
            ->willReturn(
                [
                    new MassTimeSlot(
                        $mass2->reveal(),
                        new \DateTime('2017-01-31 16:00:00'),
                        new \DateTime('2017-01-31 16:30:00')
                    ),
                    new MassTimeSlot(
                        $mass2->reveal(),
                        new \DateTime('2017-01-31 16:30:00'),
                        new \DateTime('2017-01-31 17:00:00')
                    ),
                ]
            )
        ;
        $mass2->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());

        $this
            ->isMassUnavailabilityAssignedToAllTypes
            ->handle($this->event->reveal(), $mass1->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->isMassUnavailabilityAssignedToAllTypes
            ->handle($this->event->reveal(), $mass2->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->massRepository
            ->findDispatchByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$mass1->reveal(), $mass2->reveal()])
        ;

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

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

        $this
            ->jobQueueAdapter
            ->aggregateUsersFullUnavailability(
                $this->event->reveal(),
                [
                    1 => $user1->reveal(),
                    2 => $user2->reveal(),
                    3 => $user3->reveal(),
                    4 => $user4->reveal(),
                ]
            )
            ->shouldBeCalled()
        ;

        $this
            ->userRepository
            ->findByEventWithoutDispatch($this->event->reveal(), $mass1->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $user1->reveal(),
                    $user2->reveal(),
                    $user3->reveal(),
                    $user4->reveal(),
                ]
            )
        ;

        $this
            ->userRepository
            ->findByEventWithoutDispatch($this->event->reveal(), $mass2->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $user1->reveal(),
                    $user2->reveal(),
                    $user3->reveal(),
                ]
            )
        ;

        $this
            ->typeRepository
            ->getTypesByUserIds($this->event->reveal(), [1])
            ->shouldBeCalled()
            ->willReturn([$type1->reveal()])
        ;

        $this
            ->typeRepository
            ->getTypesByUserIds($this->event->reveal(), [2])
            ->shouldBeCalled()
            ->willReturn([$type1->reveal(), $type2->reveal()])
        ;

        $this
            ->typeRepository
            ->getTypesByUserIds($this->event->reveal(), [3])
            ->shouldBeCalled()
            ->willReturn([$type2->reveal()])
        ;

        $this
            ->typeRepository
            ->getTypesByUserIds($this->event->reveal(), [4])
            ->shouldNotBeCalled()
        ;

        $mass2->hasAtLeastOneType([$type1->reveal()])->shouldBeCalled()->willReturn(true);
        $mass2->hasAtLeastOneType([$type1->reveal(), $type2->reveal()])->shouldBeCalled()->willReturn(true);
        $mass2->hasAtLeastOneType([$type2->reveal()])->shouldBeCalled()->willReturn(false);

        // mass 1
        $this
            ->massAssignmentRepository
            ->add(
                new MassAssignment(
                    $mass1->reveal(),
                    $user1->reveal(),
                    new \DateTime('2017-01-31 12:00:00'),
                    new \DateTime('2017-01-31 13:00:00')
                )
            )
            ->shouldBeCalled()
        ;
        $this
            ->massAssignmentRepository
            ->add(
                new MassAssignment(
                    $mass1->reveal(),
                    $user2->reveal(),
                    new \DateTime('2017-01-31 13:00:00'),
                    new \DateTime('2017-01-31 14:00:00')
                )
            )
            ->shouldBeCalled()
        ;
        $this
            ->massAssignmentRepository
            ->add(
                new MassAssignment(
                    $mass1->reveal(),
                    $user3->reveal(),
                    new \DateTime('2017-01-31 12:00:00'),
                    new \DateTime('2017-01-31 13:00:00')
                )
            )
            ->shouldBeCalled()
        ;
        $this
            ->massAssignmentRepository
            ->add(
                new MassAssignment(
                    $mass1->reveal(),
                    $user4->reveal(),
                    new \DateTime('2017-01-31 13:00:00'),
                    new \DateTime('2017-01-31 14:00:00')
                )
            )
            ->shouldBeCalled()
        ;

        // mass 2
        $this
            ->massAssignmentRepository
            ->add(
                new MassAssignment(
                    $mass2->reveal(),
                    $user1->reveal(),
                    new \DateTime('2017-01-31 16:00:00'),
                    new \DateTime('2017-01-31 16:30:00')
                )
            )
            ->shouldBeCalled()
        ;
        $this
            ->massAssignmentRepository
            ->add(
                new MassAssignment(
                    $mass2->reveal(),
                    $user2->reveal(),
                    new \DateTime('2017-01-31 16:30:00'),
                    new \DateTime('2017-01-31 17:00:00')
                )
            )
            ->shouldBeCalled()
        ;
        $this
            ->massAssignmentRepository
            ->add(
                new MassAssignment(
                    $mass2->reveal(),
                    $user3->reveal(),
                    new \DateTime('2017-01-31 16:00:00'),
                    new \DateTime('2017-01-31 16:30:00')
                )
            )
            ->shouldNotBeCalled()
        ;

        $this->dispatcher->dispatchAll($this->event->reveal());
    }

}
