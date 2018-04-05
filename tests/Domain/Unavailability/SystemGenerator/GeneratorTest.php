<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Unavailability\SystemGenerator;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\Generator;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\TimeRangeNotAccessibleView;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\TimeRangeView;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\System\SystemUnavailabilityForUserGeneratedEvent;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

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

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $this->availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->overlappedTimeRangeMerger = $this->prophesize(OverlappedTimeRangeMerger::class);
        $this->overlappedTimeRangeTruncater = $this->prophesize(OverlappedTimeRangeTruncater::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
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
            $this->eventDispatcher->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerateWithParticipantWithoutPackage(): void
    {
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $package1 = $this->prophesize(Package::class);
        $package2 = $this->prophesize(Package::class);
        $package1->isPassable()->willReturn(true);
        $package2->isPassable()->willReturn(false);
        $sheet1->getPackage()->willReturn($package1->reveal());
        $sheet2->getPackage()->willReturn($package2->reveal());
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $participant1->getParticipantProduct()->willReturn(null);

        $participant1->getSheet()->willReturn($sheet1->reveal());
        $participant2->getSheet()->willReturn($sheet2->reveal());

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
            $this->eventDispatcher->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerateWithParticipantWithoutProduct(): void
    {
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $package1 = $this->prophesize(Package::class);
        $package2 = $this->prophesize(Package::class);
        $package1->isPassable()->willReturn(true);
        $package2->isPassable()->willReturn(true);

        $sheet1->getPackage()->willReturn($package1->reveal());
        $sheet2->getPackage()->willReturn($package2->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $participant1->getSheet()->willReturn($sheet1->reveal());
        $participant2->getSheet()->willReturn($sheet2->reveal());

        $participant1->getParticipantProduct()->willReturn(null);
        $participant2->getParticipantProduct()->willReturn(null);

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
            $this->eventDispatcher->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerateWithParticipantWithAllAvailabilityTimeRange(): void
    {
        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $end1 = new \DateTime('2017-10-10 12:00:00.000');
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange1->getId()->willReturn(15);
        $availabilityTimeRange1->getBegin()->willReturn($begin1);
        $availabilityTimeRange1->getEnd()->willReturn($end1);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $package1 = $this->prophesize(Package::class);
        $package2 = $this->prophesize(Package::class);
        $package1->isPassable()->willReturn(true);
        $package2->isPassable()->willReturn(true);

        $sheet1->getPackage()->willReturn($package1->reveal());
        $sheet2->getPackage()->willReturn($package2->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $participant1->getSheet()->willReturn($sheet1->reveal());
        $participant2->getSheet()->willReturn($sheet2->reveal());

        $product = $this->prophesize(Product::class);
        $product->getId()->willReturn(12);
        $product->getAvailabilityTimeRanges()->willReturn([$availabilityTimeRange1->reveal()]);
        $participant1->getParticipantProduct()->willReturn($product);
        $participant2->getParticipantProduct()->willReturn($product);

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
            $this->eventDispatcher->reveal()
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
        $availabilityTimeRange1->getId()->willReturn(15);
        $availabilityTimeRange1->getBegin()->willReturn($begin1);
        $availabilityTimeRange1->getEnd()->willReturn($end1);

        $availabilityTimeRange2 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange2->getId()->willReturn(16);
        $availabilityTimeRange2->getBegin()->willReturn($begin2);
        $availabilityTimeRange2->getEnd()->willReturn($end2);

        $availabilityTimeRange3 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange3->getId()->willReturn(17);
        $availabilityTimeRange3->getBegin()->willReturn($begin3);
        $availabilityTimeRange3->getEnd()->willReturn($end3);

        $availabilityTimeRange4 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange4->getId()->willReturn(18);
        $availabilityTimeRange4->getBegin()->willReturn($begin4);
        $availabilityTimeRange4->getEnd()->willReturn($end4);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $package1 = $this->prophesize(Package::class);
        $package2 = $this->prophesize(Package::class);
        $package1->isPassable()->willReturn(true);
        $package2->isPassable()->willReturn(true);

        $sheet1->getPackage()->willReturn($package1->reveal());
        $sheet2->getPackage()->willReturn($package2->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $participant1->getSheet()->willReturn($sheet1->reveal());
        $participant2->getSheet()->willReturn($sheet2->reveal());

        $product = $this->prophesize(Product::class);
        $product->getId()->willReturn(12);
        $product->getAvailabilityTimeRanges()->willReturn([$availabilityTimeRange2->reveal()]);
        $participant1->getParticipantProduct()->willReturn($product);
        $participant2->getParticipantProduct()->willReturn($product);

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
            $this->eventDispatcher->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }
}
