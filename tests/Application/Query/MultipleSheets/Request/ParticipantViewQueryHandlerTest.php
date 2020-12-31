<?php

namespace Proximum\Vimeet\Tests\Application\Query\MultipleSheets\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $participant = $this->prophesize(Participant::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesserCache::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant->reveal(), $locale)->shouldBeCalled()->willReturn('complete Name');

        $handler = new ParticipantViewQueryHandler($participantInfoGuesser->reveal());

        $result = $handler->handle(new ParticipantViewQuery($participant->reveal(), $locale));

        $expected = new ParticipantView('complete Name');

        $this->assertEquals($expected, $result);
    }
}
