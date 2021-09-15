<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Request\RequestSheetViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Request\RequestSheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\Request\RequestParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Admin\Request\RequestSheetView;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class RequestSheetViewQueryHandlerTest extends TestCase
{
    public function test()
    {
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $meetingParticipants = $this->prophesize(MeetingParticipants::class);

        $sheet = $this->prophesize(Sheet::class);
        $request = $this->prophesize(Request::class);
        $locale = 'fr';

        $participant = $this->prophesize(Participant::class);
        $participant->getId()->willReturn(314);

        $sheet->getParticipants()->willReturn([$participant]);

        // prophecies dependencies
        $meetingParticipants->getAllMeetingParticipants($request)->willReturn([$participant->reveal()]);

        $participantInfoGuesser->guessParticipantCompleteName($participant->reveal(), 'fr')
            ->willReturn('Sophie Sonsec')
        ;

        $sheetInfoGuesser->guessSheetTitle($sheet->reveal(), 'fr')->willReturn('RATP');

        // run
        $query = new RequestSheetViewQuery($sheet->reveal(), $request->reveal(), $locale);
        $handler = new RequestSheetViewQueryHandler(
            $sheetInfoGuesser->reveal(),
            $participantInfoGuesser->reveal(),
            $meetingParticipants->reveal()
        );
        $result = $handler->handle($query);

        $expected = new RequestSheetView(
            'RATP',
            [
                new RequestParticipantView(314, 'Sophie Sonsec', true),
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
