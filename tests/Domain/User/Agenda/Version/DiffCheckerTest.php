<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class DiffCheckerTest extends TestCase
{

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $generator;

    /** @var ObjectProphecy */
    private $versionRepository;

    public function setUp()
    {
        $this->user = $this->prophesize(User::class);
        $this->event = $this->prophesize(Event::class);
        $this->generator = $this->prophesize(Generator::class);
        $this->versionRepository = $this->prophesize(VersionRepositoryInterface::class);
    }

    public function testHasDiffNoVersion()
    {
        // Expected
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        // Diff Checker
        $diffChecker = new DiffChecker($this->generator->reveal(), $this->versionRepository->reveal());
        $result = $diffChecker->hasDiff($this->event->reveal(), $this->user->reveal());

        $this->assertFalse($result);
    }

    public function testHasDiffWithDifferentVersionKey()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            1 => ['request' => 1],
            2 => ['request' => 2],
            3 => ['request' => 3],
        ]);
        // Expected
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version->reveal())
        ;
        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([
                1 => ['request' => 1],
                2 => ['request' => 2],
            ]);

        // Diff Checker
        $diffChecker = new DiffChecker($this->generator->reveal(), $this->versionRepository->reveal());
        $result = $diffChecker->hasDiff($this->event->reveal(), $this->user->reveal());

        $this->assertTrue($result);
    }

    public function testHasDiffInSlot()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ]);
        // Expected
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version->reveal())
        ;
        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([
                3 => [
                    'request' => 3,
                    'slot'    => 34567890,
                    'spot'    => 667,
                ],
            ]);

        // Diff Checker
        $diffChecker = new DiffChecker($this->generator->reveal(), $this->versionRepository->reveal());
        $result = $diffChecker->hasDiff($this->event->reveal(), $this->user->reveal());

        $this->assertTrue($result);
    }

    public function testHasDiffInSpot()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ]);
        // Expected
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version->reveal())
        ;
        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([
                3 => [
                    'request' => 3,
                    'slot'    => 1123,
                    'spot'    => 4,
                ],
            ]);

        // Diff Checker
        $diffChecker = new DiffChecker($this->generator->reveal(), $this->versionRepository->reveal());
        $result = $diffChecker->hasDiff($this->event->reveal(), $this->user->reveal());

        $this->assertTrue($result);
    }

    public function testHasNoDiff()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            1 => [
                'request' => 1,
                'slot'    => 11,
                'spot'    => 9,
            ],
            2 => [
                'request' => 2,
                'slot'    => 89,
                'spot'    => 12,
            ],
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ]);
        // Expected
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version->reveal())
        ;
        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([
                1 => [
                    'request' => 1,
                    'slot'    => 11,
                    'spot'    => 9,
                ],
                2 => [
                    'request' => 2,
                    'slot'    => 89,
                    'spot'    => 12,
                ],
                3 => [
                    'request' => 3,
                    'slot'    => 1123,
                    'spot'    => 667,
                ],
            ]);

        // Diff Checker
        $diffChecker = new DiffChecker($this->generator->reveal(), $this->versionRepository->reveal());
        $result = $diffChecker->hasDiff($this->event->reveal(), $this->user->reveal());

        $this->assertFalse($result);
    }
}
