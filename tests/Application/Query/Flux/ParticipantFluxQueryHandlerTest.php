<?php

namespace Proximum\Vimeet\Tests\Application\Query\Flux;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Flux\ParticipantFluxQuery;
use Proximum\Vimeet\Application\Query\Flux\ParticipantFluxQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Flux\ParticipantListView;
use Proximum\Vimeet\Application\View\Flux\ParticipantView;
use Proximum\Vimeet\Application\View\Flux\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantFluxQueryHandlerTest extends TestCase
{
    public function test_handle(): void
    {
        $registrationDate = new \DateTime("2019-01-01 12:00:00.000");

        $event = $this->prophesize(Event::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet1->getTypeTitle('fr')->shouldBeCalled()->willReturn('agence');

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheet2->getTypeTitle('fr')->shouldBeCalled()->willReturn('agence');
        $sheet2->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $participant1->getRegistrationDate()->shouldBeCalled()->willReturn($registrationDate);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $participant2->getRegistrationDate()->shouldBeCalled()->willReturn($registrationDate);

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getSheet()->shouldBeCalled()->willReturn($sheet2->reveal());
        $participant3->getRegistrationDate()->shouldBeCalled()->willReturn($registrationDate);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->getParticipantsByEvent($event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal()
            ]);

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetInfos($sheet1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                'sheet_title' => 'ELAO',
                'sheet_description' => 'Agence web',
                'sheet_country' => 'FR',
            ]);

        $sheetInfoGuesser->guessSheetInfos($sheet2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                'sheet_title' => 'TA Consulting',
                'sheet_description' => '',
                'sheet_country' => 'FR',
            ]);
        $sheetInfoGuesser->guessSheetLogo($sheet1->reveal(), 'fr')->shouldBeCalled()->willReturn(null);
        $sheetInfoGuesser->guessSheetLogo($sheet2->reveal(), 'fr')->shouldBeCalled()->willReturn('/uploads/mon-logo.png');

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantInfos($participant1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                'participant_firstname' => 'Mathieu',
                'participant_lastname' => 'MARCHOIS',
                'participant_position' => 'Développeur'
            ]);

        $participantInfoGuesser->guessParticipantInfos($participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                'participant_firstname' => 'Nicolas',
                'participant_lastname' => 'DIEVART',
                'participant_position' => 'Développeur'
            ]);

        $participantInfoGuesser->guessParticipantInfos($participant3->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                'participant_firstname' => 'Sébastien',
                'participant_lastname' => 'SOLERE',
                'participant_position' => 'Coach agile'
            ]);

        $eventUrlGeneratorInterface = $this->prophesize(Event\EventUrlGeneratorInterface::class);
        $eventUrlGeneratorInterface->generateBaseEventAbsoluteUrl($event->reveal())
            ->shouldBeCalled()
            ->willReturn('http://monsite');

        $handler = new ParticipantFluxQueryHandler(
            $participantRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $participantInfoGuesser->reveal(),
            $eventUrlGeneratorInterface->reveal()
        );
        $result = $handler->handle(new ParticipantFluxQuery($event->reveal(), 'fr'));
        $expectedResult = new ParticipantListView([
            new ParticipantView(
                'MM',
                'Développeur',
                $registrationDate,
                new SheetView('agence', 'ELAO', 'Agence web', 'FR', '')
            ),
            new ParticipantView(
                'ND',
                'Développeur',
                $registrationDate,
                new SheetView('agence', 'ELAO', 'Agence web', 'FR', '')
            ),
            new ParticipantView(
                'SS',
                'Coach agile',
                $registrationDate,
                new SheetView('agence', 'TA Consulting', '', 'FR', 'http://monsite/uploads/mon-logo.png')
            )
        ]);

        $this->assertEquals($expectedResult, $result);
    }
}
