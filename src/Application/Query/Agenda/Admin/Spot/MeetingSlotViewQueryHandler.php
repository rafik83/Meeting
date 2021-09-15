<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\View\Agenda\Slot\SpotMeetingSlotView;

class MeetingSlotViewQueryHandler
{
    /**
     * @var SheetInfoGuesserCache
     */
    private $sheetInfoGuesser;

    /**
     * MeetingSlotViewQueryHandler constructor.
     *
     * @param SheetInfoGuesserCache $sheetInfoGuesser
     */
    public function __construct(SheetInfoGuesserCache $sheetInfoGuesser)
    {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param MeetingSlotViewQuery $query
     *
     * @return SpotMeetingSlotView
     */
    public function handle(MeetingSlotViewQuery $query)
    {
        $sheetFromTitle = $this->sheetInfoGuesser->guessSheetTitle(
            $query->meeting->getFromSheet(), $query->locale
        );

        $sheetToTitle = $this->sheetInfoGuesser->guessSheetTitle(
            $query->meeting->getToSheet(), $query->locale
        );

        return new SpotMeetingSlotView(
            $query->meeting->getFromSheet()->getId(),
            $sheetFromTitle,
            $query->meeting->getToSheet()->getId(),
            $sheetToTitle
        );
    }
}
