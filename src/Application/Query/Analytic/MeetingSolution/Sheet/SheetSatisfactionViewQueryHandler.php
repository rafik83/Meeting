<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;

class SheetSatisfactionViewQueryHandler
{
    /**
     * @param SheetSatisfactionViewQuery $query
     *
     * @return SheetSatisfactionView
     */
    public function handle(SheetSatisfactionViewQuery $query): SheetSatisfactionView
    {
        if (0 === $query->numberOfRequest) {
            $satisfaction = 100;
        } else {
            $satisfaction = ($query->numberOfMeetings / $query->numberOfRequest) * 100;
        }

        return new SheetSatisfactionView(
            $query->sheet->getId(),
            $query->sheet->getTitle(),
            $query->sheet->getType()->getId(),
            $query->sheet->getType()->getTitle($query->locale),
            (int) round($satisfaction)
        );
    }
}
