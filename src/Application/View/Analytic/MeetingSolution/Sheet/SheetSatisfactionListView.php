<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
