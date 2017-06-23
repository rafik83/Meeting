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
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ParticipationCountTest extends TestCase
{
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
        $event = EventFactory::createEvent();
        $happening = new Happening($event, new \DateTime(),  new \DateTime(), new Happening\Category($event, '', 0, '', ''));
        $happening->setLimitParticipant($limit);

        $repository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $repository->countParticipationByHappening($happening)->shouldBeCalled()->willReturn($count);

        $service = new ParticipationCount($repository->reveal());

        $this->assertEquals($expected, $service->getRemaining($happening));
        $this->assertEquals($isFull, $service->isFull($happening));
    }

    public function testGetRemainingIfHappeningIsNull()
    {
        $event = EventFactory::createEvent();
        $happening = new Happening($event, new \DateTime(),  new \DateTime(), new Happening\Category($event, '', 0, '', ''));
        $happening->setLimitParticipant(null);

        $repository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $repository->countParticipationByHappening($happening)->shouldNotBeCalled();

        $service = new ParticipationCount($repository->reveal());

        $this->assertEquals(INF, $service->getRemaining($happening));
        $this->assertEquals(false, $service->isFull($happening));
    }
}
