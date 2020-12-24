<?php

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Preview\Resolver;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver\ParticipantsPositionResolver;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantsPositionResolverTest extends TestCase
{
    public function testHandle()
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->countParticipants()->shouldBeCalled()->willReturn(3);
        $sheet
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal(), $participant3->reveal()])
        ;

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessByTag($participant1, 'participant_position', 'fr')
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $participantInfoGuesser
            ->guessByTag($participant2, 'participant_position', 'fr')
            ->shouldBeCalled()
            ->willReturn('Directeur commercial')
        ;

        $participantInfoGuesser
            ->guessByTag($participant3, 'participant_position', 'fr')
            ->shouldNotBeCalled()
        ;

        $participantsPositionResolver = new ParticipantsPositionResolver($participantInfoGuesser->reveal());

        $this->assertEquals(
            new PreviewView(
                'custom_preview_data_participant_position',
                'Directeur commercial',
                'custom_preview_data_participant_position',
                [],
                true
            ),
            $participantsPositionResolver->handle($sheet->reveal(), 'fr')
        );
    }

    public function testHandleWithNoLink()
    {
        $participant = $this->prophesize(Participant::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->countParticipants()->shouldBeCalled()->willReturn(1);
        $sheet
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant->reveal()])
        ;

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessByTag($participant, 'participant_position', 'fr')
            ->shouldBeCalled()
            ->willReturn('Directeur marketing')
        ;

        $participantsPositionResolver = new ParticipantsPositionResolver($participantInfoGuesser->reveal());

        $this->assertEquals(
            new PreviewView(
                'custom_preview_data_participant_position',
                'Directeur marketing',
                'custom_preview_data_participant_position',
                [],
                false
            ),
            $participantsPositionResolver->handle($sheet->reveal(), 'fr')
        );
    }

    public function testHandleWithNoPosition()
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal(), $participant3->reveal()])
        ;

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessByTag($participant1, 'participant_position', 'fr')
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $participantInfoGuesser
            ->guessByTag($participant2, 'participant_position', 'fr')
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $participantInfoGuesser
            ->guessByTag($participant3, 'participant_position', 'fr')
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $participantsPositionResolver = new ParticipantsPositionResolver($participantInfoGuesser->reveal());

        $this->assertNull($participantsPositionResolver->handle($sheet->reveal(), 'fr'));
    }
}
