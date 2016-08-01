<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use Proximum\Vimeet\Application\Command\Event\ConfigureDates;
use Proximum\Vimeet\Application\Command\Event\ConfigureDatesHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfigureDatesHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        $catalogOnlineDate   = new \DateTime('2016-06-23 12:00:00');
        $happeningsOpenDate  = new \DateTime('2016-06-21 12:00:00');
        $schedulePublishDate = new \DateTime('2016-06-30 12:00:00');
            
        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setDates($catalogOnlineDate, $happeningsOpenDate, $schedulePublishDate);

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        $command                      = new ConfigureDates($event);
        $command->catalogOnlineDate   = $catalogOnlineDate;
        $command->happeningsOpenDate  = $happeningsOpenDate;
        $command->schedulePublishDate = $schedulePublishDate;

        $handler = new ConfigureDatesHandler($eventRepository->reveal());
        $handler->handle($command);
    }
}
