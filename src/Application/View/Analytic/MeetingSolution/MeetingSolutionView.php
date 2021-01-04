<?php

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionListView;

class MeetingSolutionView
{
    /** @var int */
    public $numberOfMeetings;

    /** @var int */
    public $numberOfRequests;

    /** @var int */
    public $fillingRate;

    /** @var int */
    public $ratio;

    /** @var SheetSatisfactionListView */
    public $sheetSatisfactions;

    /** @var SpotSatisfactionListView */
    public $spotSatisfactions;

    /** @var SpotFillingRateDayListView */
    public $spotFillingGraph;

    /** @var \DateTimeInterface */
    public $createdAt;

    /**
     * @param int                        $numberOfMeetings
     * @param int                        $numberOfRequests
     * @param int                        $fillingRate
     * @param SheetSatisfactionListView  $sheetSatisfaction
     * @param SpotSatisfactionListView   $spotSatisfaction
     * @param SpotFillingRateDayListView $spotFillingGraph
     * @param \DateTimeInterface         $createdAt
     */
    public function __construct(
        int $numberOfMeetings,
        int $numberOfRequests,
        int $fillingRate,
        SheetSatisfactionListView $sheetSatisfaction,
        SpotSatisfactionListView $spotSatisfaction,
        SpotFillingRateDayListView $spotFillingGraph,
        \DateTimeInterface $createdAt
    ) {
        $this->numberOfMeetings = $numberOfMeetings;
        $this->numberOfRequests = $numberOfRequests;
        $this->fillingRate = $fillingRate;
        $this->sheetSatisfactions = $sheetSatisfaction;
        $this->spotSatisfactions = $spotSatisfaction;
        $this->spotFillingGraph = $spotFillingGraph;
        $this->createdAt = $createdAt;

        if (0 === $numberOfRequests) {
            $numberOfRequests = 1;
        }

        $this->ratio = ($numberOfMeetings / $numberOfRequests) * 100;
    }
}
