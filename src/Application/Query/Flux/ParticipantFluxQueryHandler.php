<?php

namespace Proximum\Vimeet\Application\Query\Flux;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Flux\ParticipantListView;
use Proximum\Vimeet\Application\View\Flux\ParticipantView;
use Proximum\Vimeet\Application\View\Flux\SheetView;
use Proximum\Vimeet\Domain\Participant\GetParticipantInitials;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantFluxQueryHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->participantRepository = $participantRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    public function handle(ParticipantFluxQuery $query): ParticipantListView
    {
        $participants = $this->participantRepository->getParticipantsByEvent($query->event, $query->locale);
        $participantListViews = [];
        $cachedSheetViews = [];

        foreach ($participants as $participant) {
            $sheet = $participant->getSheet();

            if (!array_key_exists($sheet->getId(), $cachedSheetViews)) {
                $sheetInfos = $this->sheetInfoGuesser->guessSheetInfos($sheet, $query->locale);
                $sheetView = new SheetView(
                    $sheet->getTypeTitle($query->locale),
                    $sheetInfos[Tag::SHEET_TITLE],
                    $sheetInfos[Tag::SHEET_DESCRIPTION],
                    $sheetInfos[Tag::SHEET_COUNTRY]
                );
                $cachedSheetViews[$sheet->getId()] = $sheetView;
            } else {
                $sheetView = $cachedSheetViews[$sheet->getId()];
            }

            $participantInfo = $this->participantInfoGuesser->guessParticipantInfos($participant, $query->locale);
            $participantListViews[] = new ParticipantView(
                (new GetParticipantInitials())(
                    $participantInfo[Tag::PARTICIPANT_FIRSTNAME],
                    $participantInfo[Tag::PARTICIPANT_LASTNAME]
                ),
                $participantInfo[Tag::PARTICIPANT_POSITION],
                new \DateTime("2019-01-01 12:00:00.000"), // temp date
                $sheetView
            );
        }

        return new ParticipantListView($participantListViews);
    }
}
