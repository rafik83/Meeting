<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\SortParticipants;
use Proximum\Vimeet\Application\Command\Sheet\SortParticipantsHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class SortParticipantsHandlerTest extends TestCase
{
    public function testHandle()
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(111);
        $participant1->setRank(3)->shouldBeCalled();

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(222);
        $participant2->setRank(1)->shouldBeCalled();

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getId()->shouldBeCalled()->willReturn(333);
        $participant3->setRank(2)->shouldBeCalled();

        $sheet = $this->prophesize(Sheet::class);
        $sheet->countParticipants()->shouldBeCalled()->willReturn(3);
        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [$participant1->reveal(), $participant2->reveal(), $participant3->reveal()]
        );

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->set($participant1->reveal())->shouldBeCalled();
        $participantRepository->set($participant2->reveal())->shouldBeCalled();
        $participantRepository->set($participant3->reveal())->shouldBeCalled();

        $sortParticipants = new SortParticipants($sheet->reveal());
        $this->assertEquals(0, $sortParticipants->getParticipantRank(111));
        $this->assertEquals(1, $sortParticipants->getParticipantRank(222));
        $this->assertEquals(2, $sortParticipants->getParticipantRank(333));
        $sortParticipants->__set(111, 0);
        $sortParticipants->__set(222, 2);
        $sortParticipants->__set(333, 1);

        $sortParticipantsHandler = new SortParticipantsHandler($participantRepository->reveal());
        $sortParticipantsHandler->handle($sortParticipants);
    }
}
