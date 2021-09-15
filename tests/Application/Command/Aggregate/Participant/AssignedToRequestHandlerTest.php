<?php

namespace Proximum\Vimeet\Tests\Application\Command\Aggregate\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\AssignedToRequest;
use Proximum\Vimeet\Application\Command\Aggregate\Participant\AssignedToRequestHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantAssignedAggregator;

class AssignedToRequestHandlerTest extends TestCase
{
    public function testHandle()
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
        $participantAssignedAggregator = $this->prophesize(ParticipantAssignedAggregator::class);
        $participantAssignedAggregator
            ->aggregateAssignation($participant1->reveal())
            ->shouldBeCalled();
        $participantAssignedAggregator
            ->aggregateAssignation($participant2->reveal())
            ->shouldBeCalled();
        $participantAssignedAggregator
            ->aggregateAssignation($participant3->reveal())
            ->shouldBeCalled();

        $handler = new AssignedToRequestHandler(
            $participantRepository->reveal(),
            $participantAssignedAggregator->reveal()
        );

        $handler->handle(new AssignedToRequest($event->reveal()));
    }
}
