<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Dashboard;

class DashboardView
{
    /** @var DashboardTransactionView */
    public $transactionView;

    /** @var DashboardSheetView */
    public $dashboardSheetView;

    /** @var DashboardMeetingView */
    public $dashboardMeetingView;

    /**
     * @param DashboardTransactionView $transactionView
     * @param DashboardSheetView       $dashboardSheetView
     * @param DashboardMeetingView     $dashboardMeetingView
     */
    public function __construct(
        DashboardTransactionView $transactionView,
        DashboardSheetView $dashboardSheetView,
        DashboardMeetingView $dashboardMeetingView
    ) {
        $this->transactionView    = $transactionView;
        $this->dashboardSheetView = $dashboardSheetView;
        $this->dashboardMeetingView = $dashboardMeetingView;
    }
}
