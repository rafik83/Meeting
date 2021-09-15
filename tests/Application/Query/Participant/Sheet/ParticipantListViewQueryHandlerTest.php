<?php

namespace Proximum\Vimeet\Tests\Application\Query\Participant\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Participant\Sheet\ParticipantListViewQuery;
use Proximum\Vimeet\Application\Query\Participant\Sheet\ParticipantListViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\Sheet\ParticipantListView;
use Proximum\Vimeet\Application\View\Participant\Sheet\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $locale = 'fr';
        $participant->getId()->shouldBeCalled()->willReturn(12);

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(13);
        $participant2->getId()->shouldBeCalled()->willReturn(14);
        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn([
            $participant1->reveal(),
            $participant->reveal(),
            $participant2->reveal(),
        ]);

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessParticipantCompleteName($participant1, $locale)
            ->shouldBeCalled()
            ->willReturn('Jules Verne')
        ;
        $participantInfoGuesser
            ->guessParticipantCompleteName($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('Marie Curie')
        ;
        $participantInfoGuesser
            ->guessParticipantCompleteName($participant2, $locale)
            ->shouldBeCalled()
            ->willReturn('Albert Einstein')
        ;

        $handler = new ParticipantListViewQueryHandler($participantInfoGuesser->reveal());
        $result = $handler->handle(new ParticipantListViewQuery($sheet->reveal(), $participant->reveal(), $locale));

        $currentParticipant = new ParticipantView(12, 'Marie Curie');
        $expected = new ParticipantListView(
            $currentParticipant,
            [
                new ParticipantView(13, 'Jules Verne'),
                $currentParticipant,
                new ParticipantView(14, 'Albert Einstein'),
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
