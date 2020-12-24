<?php

namespace Proximum\Vimeet\Tests\Domain\Unavailability\SystemGenerator;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Repository\CartRowParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\Generator;
use Proximum\Vimeet\Domain\Time\TimeRangeNotAccessibleView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\System\SystemUnavailabilityForUserGeneratedEvent;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GeneratorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $unavailabilityRepository;

    /** @var ObjectProphecy */
    private $availabilityTimeRangeRepository;

    /** @var ObjectProphecy */
    private $participantRepository;

    /** @var ObjectProphecy */
    private $overlappedTimeRangeMerger;

    /** @var ObjectProphecy */
    private $overlappedTimeRangeTruncater;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $cartRowParticipantRepository;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $this->availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->overlappedTimeRangeMerger = $this->prophesize(OverlappedTimeRangeMerger::class);
        $this->overlappedTimeRangeTruncater = $this->prophesize(OverlappedTimeRangeTruncater::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->cartRowParticipantRepository = $this->prophesize(CartRowParticipantRepositoryInterface::class);
    }

    public function testGenerateWithoutAvailabilityTimeRange(): void
    {
        $availabilityTimeRanges = [];

        $this->unavailabilityRepository
            ->removeSystemUnavailabilityForUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
        ;

        $this->availabilityTimeRangeRepository
            ->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($availabilityTimeRanges)
        ;

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldNotBeCalled()
        ;

        $this->overlappedTimeRangeMerger
            ->merge(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->overlappedTimeRangeTruncater
            ->truncate(Argument::any(), Argument::any())
            ->shouldNotBeCalled()
        ;

        $dispatchedEvent = new SystemUnavailabilityForUserGeneratedEvent($this->user->reveal(), $this->event->reveal());
        $this->eventDispatcher
            ->dispatch(Events::USER_SYSTEM_UNAVAILABILITY_GENERATED, $dispatchedEvent)
            ->shouldBeCalled()
        ;

        $generator = new Generator(
            $this->unavailabilityRepository->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->eventDispatcher->reveal(),
            $this->cartRowParticipantRepository->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerateWithParticipantWithoutPackage(): void
    {
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $availabilityTimeRanges = [
            $availabilityTimeRange1->reveal(),
        ];
        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        $this->unavailabilityRepository
            ->removeSystemUnavailabilityForUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
        ;

        $this->availabilityTimeRangeRepository
            ->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($availabilityTimeRanges)
        ;

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participants)
        ;

        $this->overlappedTimeRangeMerger
            ->merge(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->overlappedTimeRangeTruncater
            ->truncate(Argument::any(), Argument::any())
            ->shouldNotBeCalled()
        ;

        $dispatchedEvent = new SystemUnavailabilityForUserGeneratedEvent($this->user->reveal(), $this->event->reveal());
        $this->eventDispatcher
            ->dispatch(Events::USER_SYSTEM_UNAVAILABILITY_GENERATED, $dispatchedEvent)
            ->shouldBeCalled()
        ;

        $generator = new Generator(
            $this->unavailabilityRepository->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->eventDispatcher->reveal(),
            $this->cartRowParticipantRepository->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerateWithParticipantWithoutProduct(): void
    {
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $availabilityTimeRanges = [
            $availabilityTimeRange1->reveal(),
        ];
        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        $this->unavailabilityRepository
            ->removeSystemUnavailabilityForUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
        ;

        $this->availabilityTimeRangeRepository
            ->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($availabilityTimeRanges)
        ;

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participants)
        ;

        $this->overlappedTimeRangeMerger
            ->merge(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->overlappedTimeRangeTruncater
            ->truncate(Argument::any(), Argument::any())
            ->shouldNotBeCalled()
        ;

        $dispatchedEvent = new SystemUnavailabilityForUserGeneratedEvent($this->user->reveal(), $this->event->reveal());
        $this->eventDispatcher
            ->dispatch(Events::USER_SYSTEM_UNAVAILABILITY_GENERATED, $dispatchedEvent)
            ->shouldBeCalled()
        ;

        $generator = new Generator(
            $this->unavailabilityRepository->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->eventDispatcher->reveal(),
            $this->cartRowParticipantRepository->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerateWithParticipantWithAllAvailabilityTimeRange(): void
    {
        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $end1 = new \DateTime('2017-10-10 12:00:00.000');
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange1->getId()->shouldBeCalled()->willReturn(15);
        $availabilityTimeRange1->getBegin()->shouldBeCalled()->willReturn($begin1);
        $availabilityTimeRange1->getEnd()->shouldBeCalled()->willReturn($end1);

        $product = $this->prophesize(Product::class);
        $product->getId()->shouldBeCalled()->willReturn(12);
        $product->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn([$availabilityTimeRange1->reveal()]);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getParticipantProduct()->shouldBeCalled()->willReturn($product);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn($product);

        $availabilityTimeRanges = [
            $availabilityTimeRange1->reveal(),
        ];
        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        $this->unavailabilityRepository
            ->removeSystemUnavailabilityForUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
        ;

        $this->availabilityTimeRangeRepository
            ->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($availabilityTimeRanges)
        ;

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participants)
        ;

        $this->overlappedTimeRangeMerger
            ->merge(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->overlappedTimeRangeTruncater
            ->truncate(Argument::any(), Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->unavailabilityRepository
            ->add(Argument::any())
            ->shouldNotBeCalled()
        ;

        $dispatchedEvent = new SystemUnavailabilityForUserGeneratedEvent($this->user->reveal(), $this->event->reveal());
        $this->eventDispatcher
            ->dispatch(Events::USER_SYSTEM_UNAVAILABILITY_GENERATED, $dispatchedEvent)
            ->shouldBeCalled()
        ;

        $generator = new Generator(
            $this->unavailabilityRepository->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->eventDispatcher->reveal(),
            $this->cartRowParticipantRepository->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerate(): void
    {
        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $end1 = new \DateTime('2017-10-10 12:00:00.000');
        $begin2 = new \DateTime('2017-10-10 14:00:00.000');
        $end2 = new \DateTime('2017-10-10 16:00:00.000');
        $begin3 = new \DateTime('2017-10-10 14:30:00.000');
        $end3 = new \DateTime('2017-10-10 18:00:00.000');
        $begin4 = new \DateTime('2017-10-10 19:00:00.000');
        $end4 = new \DateTime('2017-10-10 20:00:00.000');

        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange1->getId()->shouldBeCalled()->willReturn(15);
        $availabilityTimeRange1->getBegin()->shouldBeCalled()->willReturn($begin1);
        $availabilityTimeRange1->getEnd()->shouldBeCalled()->willReturn($end1);

        $availabilityTimeRange2 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange2->getId()->shouldBeCalled()->willReturn(16);
        $availabilityTimeRange2->getBegin()->shouldBeCalled()->willReturn($begin2);
        $availabilityTimeRange2->getEnd()->shouldBeCalled()->willReturn($end2);

        $availabilityTimeRange3 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange3->getId()->shouldBeCalled()->willReturn(17);
        $availabilityTimeRange3->getBegin()->shouldBeCalled()->willReturn($begin3);
        $availabilityTimeRange3->getEnd()->shouldBeCalled()->willReturn($end3);

        $availabilityTimeRange4 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange4->getId()->shouldBeCalled()->willReturn(18);
        $availabilityTimeRange4->getBegin()->shouldBeCalled()->willReturn($begin4);
        $availabilityTimeRange4->getEnd()->shouldBeCalled()->willReturn($end4);

        $product = $this->prophesize(Product::class);
        $product->getId()->shouldBeCalled()->willReturn(12);
        $product->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn([$availabilityTimeRange2->reveal()]);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getParticipantProduct()->shouldBeCalled()->willReturn($product->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn($product->reveal());

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRowParticipant = $this->prophesize(CartRowParticipant::class);
        $cartRowParticipant->getCartRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $cartRow->getProduct()->shouldBeCalled()->willReturn($product);
        $this->cartRowParticipantRepository
            ->findByParticipant($participant3->reveal())
            ->shouldBeCalled()
            ->willReturn($cartRowParticipant->reveal())
        ;

        $availabilityTimeRanges = [
            $availabilityTimeRange1->reveal(),
            $availabilityTimeRange2->reveal(),
            $availabilityTimeRange3->reveal(),
            $availabilityTimeRange4->reveal(),
        ];
        $timeRangeAccessbile = new TimeRangeView($begin2, $end2);
        $timeRangeNotAccessible1 = new TimeRangeNotAccessibleView($begin1, $end1);
        $timeRangeNotAccessible3 = new TimeRangeNotAccessibleView($begin3, $end3);
        $timeRangeNotAccessible4 = new TimeRangeNotAccessibleView($begin4, $end4);
        $timeRangeNotAccessibleTruncated = new TimeRangeNotAccessibleView($end2, $end3);
        $notAccessibleAvailabilityTimeRanges = [
            $timeRangeNotAccessible1,
            $timeRangeNotAccessible3,
            $timeRangeNotAccessible4,
        ];
        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
            $participant3->reveal(),
        ];

        $this->unavailabilityRepository
            ->removeSystemUnavailabilityForUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
        ;

        $this->availabilityTimeRangeRepository
            ->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($availabilityTimeRanges)
        ;

        $this->participantRepository
            ->getAllParticipantForUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participants)
        ;

        $this->overlappedTimeRangeMerger
            ->merge($notAccessibleAvailabilityTimeRanges)
            ->shouldBeCalled()
            ->willReturn($notAccessibleAvailabilityTimeRanges)
        ;

        $this->overlappedTimeRangeTruncater
            ->truncate($timeRangeNotAccessible1, [$timeRangeAccessbile])
            ->shouldBeCalled()
            ->willReturn([$timeRangeNotAccessible1])
        ;
        $this->overlappedTimeRangeTruncater
            ->truncate($timeRangeNotAccessible3, [$timeRangeAccessbile])
            ->shouldBeCalled()
            ->willReturn([$timeRangeNotAccessibleTruncated])
        ;
        $this->overlappedTimeRangeTruncater
            ->truncate($timeRangeNotAccessible4, [$timeRangeAccessbile])
            ->shouldBeCalled()
            ->willReturn([$timeRangeNotAccessible4])
        ;

        $unavailability1 = new Unavailability(
            $this->user->reveal(),
            $this->event->reveal(),
            $begin1,
            $end1,
            null,
            Unavailability::CREATED_BY_SYSTEM
        );
        $unavailability2 = new Unavailability(
            $this->user->reveal(),
            $this->event->reveal(),
            $end2,
            $end3,
            null,
            Unavailability::CREATED_BY_SYSTEM
        );
        $unavailability3 = new Unavailability(
            $this->user->reveal(),
            $this->event->reveal(),
            $begin4,
            $end4,
            null,
            Unavailability::CREATED_BY_SYSTEM
        );

        $this->unavailabilityRepository->add($unavailability1)->shouldBeCalled();
        $this->unavailabilityRepository->add($unavailability2)->shouldBeCalled();
        $this->unavailabilityRepository->add($unavailability3)->shouldBeCalled();

        $dispatchedEvent = new SystemUnavailabilityForUserGeneratedEvent($this->user->reveal(), $this->event->reveal());
        $this->eventDispatcher
            ->dispatch(Events::USER_SYSTEM_UNAVAILABILITY_GENERATED, $dispatchedEvent)
            ->shouldBeCalled()
        ;

        $generator = new Generator(
            $this->unavailabilityRepository->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->overlappedTimeRangeMerger->reveal(),
            $this->overlappedTimeRangeTruncater->reveal(),
            $this->eventDispatcher->reveal(),
            $this->cartRowParticipantRepository->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }
}
