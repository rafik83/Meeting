<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenParticipants;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\FullUnavailabilityForGivenParticipantsHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityForGivenParticipantsHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository
            ->findByIds([1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal(),
            ])
        ;

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

        $handler = new FullUnavailabilityForGivenParticipantsHandler(
            $participantRepository->reveal(),
            $participantFullUnavailabilityAggregator->reveal()
        );

        $handler->handle(new FullUnavailabilityForGivenParticipants([1, 2, 3]));
    }
}
