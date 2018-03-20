<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardView;

class DashboardViewQueryHandler
{
    /** @var DashboardTransactionViewQueryHandler */
    private $dashboardTransactionViewQueryHandler;

    /** @var DashboardSheetViewQueryHandler */
    private $dashboardSheetViewQueryHandler;

    /** @var DashboardMeetingViewQueryHandler */
    private $dashboardMeetingViewQueryHandler;

    /**
     * @param DashboardTransactionViewQueryHandler $dashboardTransactionViewQueryHandler
     * @param DashboardSheetViewQueryHandler       $dashboardSheetViewQueryHandler
     * @param DashboardMeetingViewQueryHandler     $dashboardMeetingViewQueryHandler
     */
    public function __construct(
        DashboardTransactionViewQueryHandler $dashboardTransactionViewQueryHandler,
        DashboardSheetViewQueryHandler $dashboardSheetViewQueryHandler,
        DashboardMeetingViewQueryHandler $dashboardMeetingViewQueryHandler
    ) {
        $this->dashboardTransactionViewQueryHandler = $dashboardTransactionViewQueryHandler;
        $this->dashboardSheetViewQueryHandler       = $dashboardSheetViewQueryHandler;
        $this->dashboardMeetingViewQueryHandler = $dashboardMeetingViewQueryHandler;
    }

    /**
     * @param DashboardViewQuery $dashboardViewQuery
     *
     * @return DashboardView
     */
    public function handle(DashboardViewQuery $dashboardViewQuery): DashboardView
    {
        $dashboardTransactionView = $this->dashboardTransactionViewQueryHandler->handle(
            new DashboardTransactionViewQuery($dashboardViewQuery->event)
        );

        $dashboardSheetView = $this->dashboardSheetViewQueryHandler->handle(
            new DashboardSheetViewQuery($dashboardViewQuery->event, $dashboardViewQuery->locale)
        );

        $dashboardMeetingView = $this->dashboardMeetingViewQueryHandler->handle(
            new DashboardMeetingViewQuery($dashboardViewQuery->event)
        );

        return new DashboardView($dashboardTransactionView, $dashboardSheetView, $dashboardMeetingView);
    }
}
