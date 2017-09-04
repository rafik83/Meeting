<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
