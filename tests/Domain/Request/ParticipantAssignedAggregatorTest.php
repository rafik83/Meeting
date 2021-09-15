<?php

namespace Proximum\Vimeet\Tests\Domain\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantAssignedAggregator;

class ParticipantAssignedAggregatorTest extends TestCase
{
    public function testAggregateNoRequest()
    {
        $participant = $this->prophesize(Participant::class);
        $participant->setHasRequestAssigned(false)->shouldBeCalled();

        $requestRepository     = $this->prophesize(RequestRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $requestRepository->participantIsAssignedToAccepted($participant->reveal())->shouldBeCalled()->willReturn(false);
        $participantRepository->set($participant->reveal())->shouldBeCalled();

        $participantAssignedAggregator = new ParticipantAssignedAggregator(
            $requestRepository->reveal(),
            $participantRepository->reveal()
        );

        $participantAssignedAggregator->aggregateAssignation($participant->reveal());
    }

    public function testAggregateWithRequest()
    {
        $participant = $this->prophesize(Participant::class);
        $participant->setHasRequestAssigned(true)->shouldBeCalled();

        $requestRepository     = $this->prophesize(RequestRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $requestRepository->participantIsAssignedToAccepted($participant->reveal())->shouldBeCalled()->willReturn(true);
        $participantRepository->set($participant->reveal())->shouldBeCalled();

        $participantAssignedAggregator = new ParticipantAssignedAggregator(
            $requestRepository->reveal(),
            $participantRepository->reveal()
        );

        $participantAssignedAggregator->aggregateAssignation($participant->reveal());
    }
}
