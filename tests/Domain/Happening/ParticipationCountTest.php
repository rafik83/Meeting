<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ParticipationCountTest extends TestCase
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var ObjectProphecy
     */
    private $happeningParticipationRepository;

    public function setUp()
    {
        $this->event                            = EventFactory::class;
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
    }

    public static function provideGetRemaining()
    {
        return [
            [3, 10, 7, false],
            [0, 10, 10, true],
            [0, 10, 12, true],
            [10, 10, 0, false],
        ];
    }

    /**
     * @dataProvider provideGetRemaining
     *
     * @param int  $expected The expected remaining count
     * @param int  $limit    The happening participant limit
     * @param int  $count    The happening participant count
     * @param bool $isFull   Is the happening full
     */
    public function testGetRemaining(int $expected, int $limit, int $count, bool $isFull)
    {
        $happening = new Happening($this->event, new \DateTime(),  new \DateTime(), new Happening\Category($this->event, '', 0, '', ''));
        $happening->setLimitParticipant($limit);

        $this->happeningParticipationRepository->countParticipationByHappening($happening)->shouldBeCalled()->willReturn($count);

        $service = new ParticipationCount($this->happeningParticipationRepository->reveal());

        $this->assertEquals($expected, $service->getRemaining($happening));
        $this->assertEquals($isFull, $service->isFull($happening));
    }

    public function testGetRemainingIfHappeningIsNull()
    {
        $happening = new Happening($this->event, new \DateTime(),  new \DateTime(), new Happening\Category($this->event, '', 0, '', ''));
        $happening->setLimitParticipant(null);

        $this->happeningParticipationRepository->countParticipationByHappening($happening)->shouldNotBeCalled();

        $service = new ParticipationCount($this->happeningParticipationRepository->reveal());

        $this->assertEquals(INF, $service->getRemaining($happening));
        $this->assertEquals(false, $service->isFull($happening));
    }
}
