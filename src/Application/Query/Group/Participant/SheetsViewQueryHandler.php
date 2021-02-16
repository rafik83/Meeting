<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\ParticipantView;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\SheetView;

class SheetsViewQueryHandler
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantViewQueryHandler
     */
    private $participantViewQueryHandler;

    /**
     * SheetsViewQueryHandler constructor.
     *
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param ParticipantViewQueryHandler $participantViewQueryHandler
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantViewQueryHandler $participantViewQueryHandler
    ) {
        $this->sheetInfoGuesser            = $sheetInfoGuesser;
        $this->participantViewQueryHandler = $participantViewQueryHandler;
    }

    /**
     * @param SheetsViewQuery $query
     *
     * @return array
     */
    public function handle(SheetsViewQuery $query)
    {
        $sheetViews = [];
        foreach ($query->sheets as $sheet) {
            $participantViews = [];
            foreach ($sheet->getParticipants()->toArray() as $participant) {
                $participantViews[] = $this
                    ->participantViewQueryHandler
                    ->handle(new ParticipantViewQuery($participant, $sheet->getEvent(), $query->eventDays));
            }

            usort($participantViews,
                function (ParticipantView $one, ParticipantView $other) {
                    return strcasecmp($one->lastName, $other->lastName);
                }
            );

            $sheetViews[] = new SheetView(
                $sheet->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($sheet),
                $participantViews
            );
        }

        usort($sheetViews,
            function (SheetView $one, SheetView $other) {
                return strcasecmp($one->title, $other->title);
            }
        );

        return $sheetViews;
    }
}
