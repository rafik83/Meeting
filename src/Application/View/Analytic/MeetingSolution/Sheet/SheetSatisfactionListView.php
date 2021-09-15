<?php

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet;

class SheetSatisfactionListView
{
    /** @var SheetSatisfactionView[] */
    public $sheetSatisfaction;

    public function __construct()
    {
        $this->sheetSatisfaction = [];
    }

    /**
     * @param SheetSatisfactionView $satisfactionView
     */
    public function addSheetSatisfaction(SheetSatisfactionView $satisfactionView)
    {
        $this->sheetSatisfaction[] = $satisfactionView;
    }
}
