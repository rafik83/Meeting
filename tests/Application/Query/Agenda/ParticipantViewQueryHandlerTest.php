<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event        = EventFactory::createEvent();
        $sheet        = SheetFactory::create($event);
        $participant  = ParticipantFactory::create($sheet);
        $participant2 = ParticipantFactory::create($sheet);

        // Reflection
        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 1);
        $property->setValue($participant2, 2);
        $property->setAccessible(false);

        // Mock
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant, 'fr')->shouldBeCalled()->willReturn('jean paul');
        $participantInfoGuesser->guessParticipantCompleteName($participant2, 'fr')->shouldBeCalled()->willReturn('truc muche');

        // Handler
        $participantHandler = new ParticipantViewQueryHandler($participantInfoGuesser->reveal());
        $result = $participantHandler->handle(new ParticipantViewQuery([$participant, $participant2], 'fr'));

        // Expected
        $expected = [
            new ParticipantView(1, 'jean paul'),
            new ParticipantView(2, 'truc muche'),
        ];

        // Assert
        $this->assertEquals($expected, $result);
    }
}
