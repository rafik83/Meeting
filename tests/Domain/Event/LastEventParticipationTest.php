<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Domain\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\LastEventParticipation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class LastEventParticipationTest extends TestCase
{
    public function testGetLastEvent()
    {
        $user = UserFactory::create();
        $currentEvent = EventFactory::createEvent();
        $lastEvent = EventFactory::createEvent();

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $datetime = new \DateTime();

        $eventRepository->getLastEventParticipation($user, $currentEvent)
            ->shouldBeCalled()
            ->willReturn($lastEvent);

        $lastEventParticipation = new LastEventParticipation(
            $eventRepository->reveal(),
            $datetime
        );

        $lastEventResult = $lastEventParticipation->getLastEvent($user, $currentEvent);

        $this->assertEquals($lastEvent, $lastEventResult);
    }
}
