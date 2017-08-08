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
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class LastEventParticipationTest extends TestCase
{
    public function testGetLastEvent()
    {
        $user = UserFactory::create();
        $currentEvent = EventFactory::createEvent();
        $sheet = SheetFactory::create($currentEvent);
        $lastParticipation = ParticipantFactory::create($sheet, $user);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $datetime = new \DateTime();

        $participantRepository->getLastEventParticipation($user, $currentEvent)
            ->shouldBeCalled()
            ->willReturn($lastParticipation);

        $lastEventParticipation = new LastEventParticipation(
            $participantRepository->reveal(),
            $datetime
        );

        $lastParticipationResult = $lastEventParticipation->getLastEvent($user, $currentEvent);

        $this->assertEquals($lastParticipation, $lastParticipationResult);
    }
}
