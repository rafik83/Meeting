<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\Day;

use Proximum\Vimeet\Application\Command\Event\Day\Update;
use Proximum\Vimeet\Application\Command\Event\Day\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *  Add one day to the event
     */
    public function testHandleWithOneDay()
    {
        $event = EventFactory::createEvent();

        $starTime1 = new \DateTime('24-12-2016 08:00:00.000');
        $endTime1  = new \DateTime('24-12-2016 16:00:00.000');

        // Expected
        $expectedDay = new Day(
            $event,
            new \DateTime('24-12-2016 08:00:00.000'),
            new \DateTime('24-12-2016 16:00:00.000')
        );

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->removeFromEvent($event)->shouldBeCalled();
        $dayRepository->add($expectedDay)->shouldBeCalled();

        // Data
        $update  = new Update($event);
        $update->days[] = [
            'startTime' => $starTime1,
            'endTime'   => $endTime1,
        ];

        $handler = new UpdateHandler($dayRepository->reveal());
        $handler->handle($update);
    }

    /**
     *  Add three days to the event
     */
    public function testHandleWithThreeDays()
    {
        $event = EventFactory::createEvent();

        $starTime1 = new \DateTime('24-12-2016 08:00:00.000');
        $endTime1  = new \DateTime('24-12-2016 16:00:00.000');

        $starTime2 = new \DateTime('25-12-2016 10:00:00.000');
        $endTime2  = new \DateTime('25-12-2016 18:00:00.000');

        $starTime3 = new \DateTime('26-12-2016 12:30:00.000');
        $endTime3  = new \DateTime('26-12-2016 20:45:00.000');


        // Expected
        $expectedDay1 = new Day(
            $event,
            new \DateTime('24-12-2016 08:00:00.000'),
            new \DateTime('24-12-2016 16:00:00.000')
        );
        $expectedDay2 = new Day(
            $event,
            new \DateTime('25-12-2016 10:00:00.000'),
            new \DateTime('25-12-2016 18:00:00.000')
        );
        $expectedDay3 = new Day(
            $event,
            new \DateTime('26-12-2016 12:30:00.000'),
            new \DateTime('26-12-2016 20:45:00.000')
        );

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->removeFromEvent($event)->shouldBeCalled();
        $dayRepository->add($expectedDay1)->shouldBeCalled();
        $dayRepository->add($expectedDay2)->shouldBeCalled();
        $dayRepository->add($expectedDay3)->shouldBeCalled();

        // Data
        $update  = new Update($event);
        $update->days[] = [
            'startTime' => $starTime1,
            'endTime'   => $endTime1,
        ];
        $update->days[] = [
            'startTime' => $starTime2,
            'endTime'   => $endTime2,
        ];
        $update->days[] = [
            'startTime' => $starTime3,
            'endTime'   => $endTime3,
        ];

        $handler = new UpdateHandler($dayRepository->reveal());
        $handler->handle($update);
    }

    /**
     *  Remove all the days of the event
     */
    public function testHandleWithoutDays()
    {
        $event = EventFactory::createEvent();

        // Unexpected
        $unExpectedDay = new Day(
            $event,
            new \DateTime('24-12-2016 08:00:00.000'),
            new \DateTime('24-12-2016 16:00:00.000')
        );

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->removeFromEvent($event)->shouldBeCalled();
        $dayRepository->add($unExpectedDay)->shouldNotBeCalled();

        // Data
        $update = new Update($event);

        $handler = new UpdateHandler($dayRepository->reveal());
        $handler->handle($update);
    }
}
