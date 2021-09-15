<?php

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot;

class SpotSatisfactionListView
{
    /** @var SpotSatisfactionView[] */
    public $spotSatisfaction;

    public function __construct()
    {
        $this->spotSatisfaction = [];
    }

    /**
     * @param SpotSatisfactionView $satisfactionView
     */
    public function addSpotSatisfaction(SpotSatisfactionView $satisfactionView)
    {
        $this->spotSatisfaction[] = $satisfactionView;
    }
}
