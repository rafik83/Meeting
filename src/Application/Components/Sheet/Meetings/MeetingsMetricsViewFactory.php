<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Meetings;

class MeetingsMetricsViewFactory
{
    /**
     * @param SheetMeetingsListView[] $sheetMeetingsListViews
     *
     * @return MeetingsMetricsView
     */
    public function getFromSheets(array $sheetMeetingsListViews)
    {
        $meetingsTotal                           = 0;
        $requestsTotal                           = 0;
        $slotsTotal                              = 0;
        $fillingTotal                            = 0;
        $requestsPropositionsTransformationTotal = 0;
        $sheetsTotalForRequestsTransformation    = 0;
        $sheetsTotal                             = count($sheetMeetingsListViews);

        foreach ($sheetMeetingsListViews as $sheet) {
            $meetingsTotal += $sheet->meetingsRequestsNumber;
            $requestsTotal += $sheet->requestsNumber;
            $slotsTotal += $sheet->availableSlots;
            $fillingTotal += $sheet->filling;

            if ($sheet->requestsNumber) {
                $requestsPropositionsTransformationTotal += $sheet->requestsPropositionsTransformation;
                ++$sheetsTotalForRequestsTransformation;
            }
        }

        $transformationTotal = !$requestsTotal ? 0 : 100 * $meetingsTotal / $requestsTotal;
        $averageFilling      = !$sheetsTotal ? 0 : $fillingTotal / $sheetsTotal;

        $averageRequestsPropositionsTransformation = !$sheetsTotalForRequestsTransformation
            ? 0
            : $requestsPropositionsTransformationTotal / $sheetsTotalForRequestsTransformation;

        return new MeetingsMetricsView(
            $sheetsTotal,
            $meetingsTotal,
            $requestsTotal,
            $transformationTotal,
            $averageFilling,
            $averageRequestsPropositionsTransformation
        );
    }
}
