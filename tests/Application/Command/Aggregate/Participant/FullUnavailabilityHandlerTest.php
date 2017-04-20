<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailability;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleOnlyCatalog()
    {
        $event = $this->prophesize(Event::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository
            ->findByEventAndInCatalog($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal(),
            ])
        ;

        $participantRepository
            ->findByEvent($event->reveal())
            ->shouldNotBeCalled();

        $participantFullUnavailabilityAggregator = $this->prophesize(ParticipantUnavailableAggregator::class);
        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($participant1->reveal())
            ->shouldBeCalled();
        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($participant2->reveal())
            ->shouldBeCalled();
        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($participant3->reveal())
            ->shouldBeCalled();

        $handler = new FullUnavailabilityHandler(
            $participantRepository->reveal(),
            $participantFullUnavailabilityAggregator->reveal()
        );

        $handler->handle(new FullUnavailability($event->reveal(), true));
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal(),
            ])
        ;

        $participantRepository
            ->findByEventAndInCatalog($event->reveal())
            ->shouldNotBeCalled();

        $participantFullUnavailabilityAggregator = $this->prophesize(ParticipantUnavailableAggregator::class);
        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($participant1->reveal())
            ->shouldBeCalled();
        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($participant2->reveal())
            ->shouldBeCalled();
        $participantFullUnavailabilityAggregator
            ->aggregateUnavailability($participant3->reveal())
            ->shouldBeCalled();

        $handler = new FullUnavailabilityHandler(
            $participantRepository->reveal(),
            $participantFullUnavailabilityAggregator->reveal()
        );

        $handler->handle(new FullUnavailability($event->reveal(), false));
    }
}
