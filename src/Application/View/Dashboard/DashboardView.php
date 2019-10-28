<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Dashboard;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardContactView;

class DashboardView
{
    /** @var DashboardTransactionView */
    public $transactionView;

    /** @var DashboardSheetView */
    public $dashboardSheetView;

    /** @var DashboardMeetingView */
    public $dashboardMeetingView;

    /** @var DashboardContactView */
    public $dashboardContactView;

    public function __construct(
        DashboardTransactionView $transactionView,
        DashboardSheetView $dashboardSheetView,
        DashboardMeetingView $dashboardMeetingView,
        DashboardContactView $dashboardContactView
    ) {
        $this->transactionView    = $transactionView;
        $this->dashboardSheetView = $dashboardSheetView;
        $this->dashboardMeetingView = $dashboardMeetingView;
        $this->dashboardContactView = $dashboardContactView;
    }
}
