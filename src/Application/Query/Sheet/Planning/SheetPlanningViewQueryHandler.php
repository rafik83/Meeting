<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Planning;

use Proximum\Vimeet\Application\Components\Planning\Displayer\SheetPlanningDisplayer;
use Proximum\Vimeet\Application\View\Sheet\Planning\SheetPlanningView;

class SheetPlanningViewQueryHandler
{
    /**
     * @var SheetPlanningDisplayer
     */
    private $sheetPlanningDisplayer;

    /**
     * @param SheetPlanningDisplayer $sheetPlanningDisplayer
     */
    public function __construct(SheetPlanningDisplayer $sheetPlanningDisplayer)
    {
        $this->sheetPlanningDisplayer = $sheetPlanningDisplayer;
    }

    /**
     * @param SheetPlanningViewQuery $query
     *
     * @return SheetPlanningView
     */
    public function handle(SheetPlanningViewQuery $query)
    {
        $planning = $this->sheetPlanningDisplayer->display(
            $query->sheet,
            $query->userLocale,
            $query->currentParticipant
        );

        return new SheetPlanningView($planning);
    }
}
