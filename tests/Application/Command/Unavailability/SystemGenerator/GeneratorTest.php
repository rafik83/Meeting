<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\SystemGenerator;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Unavailability\SystemGenerator\Generator;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
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

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $this->availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
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

        $generator = new Generator(
            $this->unavailabilityRepository->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantRepository->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }

    public function testGenerateWithParticipantWithoutPackage(): void
    {
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

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

        $generator = new Generator(
            $this->unavailabilityRepository->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantRepository->reveal()
        );

        $generator->generateSystemUnavailability($this->event->reveal(), $this->user->reveal());
    }
}
