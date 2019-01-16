<?php

namespace Proximum\Vimeet\Tests\Application\Query\Flux;

use Proximum\Vimeet\Application\Query\Flux\ParticipantFluxQuery;
use Proximum\Vimeet\Application\Query\Flux\ParticipantFluxQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Flux\ParticipantListView;
use Proximum\Vimeet\Application\View\Flux\ParticipantView;
use Proximum\Vimeet\Application\View\Flux\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantFluxQueryHandlerTest extends TestCase
{
    public function test_handle(): void
    {
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $event = $this->prophesize(Event::class);
        $registrationDate = new \DateTime();

        $handler = new ParticipantFluxQueryHandler($participantRepository->reveal());
        $result = $handler->handle(new ParticipantFluxQuery($event->reveal()));
        $expectedResult = new ParticipantListView([
            new ParticipantView(
                'MM',
                'Developer',
                $registrationDate,
                new SheetView('ELAO', 'Web agency', 'agency', 'FR')
            ),
            new ParticipantView(
                'ND',
                'Developer',
                $registrationDate,
                new SheetView('ELAO', 'Web agency', 'agency', 'FR')
            ),
            new ParticipantView(
                'SS',
                'Coach',
                $registrationDate,
                new SheetView('TA', '', 'consulting', 'FR')
            )
        ]);

        $this->assertEquals($expectedResult, $result);
    }
}
