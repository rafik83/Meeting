<?php

namespace Proximum\Vimeet\Tests\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ParticipantWithAttributedProductUpdated;
use PHPUnit\Framework\TestCase;

class ParticipantWithAttributedProductUpdatedTest extends TestCase
{
    public function testGetFilteredByParticipants()
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(1);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->willReturn(2);

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getId()->willReturn(3);

        $participantWithAttributedProductUpdated = new ParticipantWithAttributedProductUpdated();
        $participantWithAttributedProductUpdated->add($participant2->reveal());
        $participantWithAttributedProductUpdated->add($participant3->reveal());

        $this->assertEquals(
            [$participant3->reveal()],
            $participantWithAttributedProductUpdated->getFilteredByParticipants(
                [
                    $participant1->reveal(),
                    $participant3->reveal(),
                ]
            )
        );
    }
}
