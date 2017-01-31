<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Prophecy\Argument;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchUnavailabilityHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $begin   = new \DateTime();
        $end     = new \DateTime();
        $spotIds = [1, 2];

        $spots = [
            new Spot('SP1', $event, 20.0, 1, 1, true),
            new Spot('SP2', $event, 20.0, 1, 1, true),
        ];

        $meetingSlot = new MeetingSlot($event, $begin, $end);

        // Command
        $command               = new UnavailabilityBatch($spotIds, $event);
        $command->meetingSlots = [$meetingSlot];

        // Mock
        $spotRepository               = $this->prophesize(SpotRepositoryInterface::class);
        $spotUnavailabilityRepository = $this->prophesize(SpotUnavailabilityRepositoryInterface::class);
        $meetingRepository            = $this->prophesize(MeetingRepositoryInterface::class);

        $spotRepository->findMany($event, $spotIds)->shouldBeCalled()->willReturn($spots);

        foreach ($spots as $spot) {
            $meetingRepository->findBySpotAndSlot($spot, $meetingSlot)->shouldBeCalled()->willReturn([]);
            $spotUnavailabilityRepository->add(Argument::that(function (SpotUnavailability $spotUnavailability) {
                return $spotUnavailability;
            }))->shouldBeCalled();
        }

        $handler = new UnavailabilityBatchHandler(
            $spotRepository->reveal(),
            $spotUnavailabilityRepository->reveal(),
            $meetingRepository->reveal()
        );

        $handler->handle($command);
    }
}
