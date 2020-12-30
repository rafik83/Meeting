<?php

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph;

class SpotFillingRateDayListView
{
    /** @var SpotFillingRateDayView[] */
    public $spotFillingRateDayView;

    public function __construct()
    {
        $this->spotFillingRateDayView = [];
    }

    /**
     * @param SpotFillingRateDayView $spotFillingRateDayView
     */
    public function addSpotFillingRateDayView(SpotFillingRateDayView $spotFillingRateDayView)
    {
        $this->spotFillingRateDayView[] = $spotFillingRateDayView;
    }
}
