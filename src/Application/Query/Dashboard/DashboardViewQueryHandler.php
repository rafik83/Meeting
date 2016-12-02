<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardTransactionView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardView;

class DashboardViewQueryHandler
{
    /**
     * @var DashboardTransactionViewQueryHandler
     */
    private $dashboardTransactionViewQueryHandler;

    /**
     * @var DashboardSheetViewQueryHandler
     */
    private $dashboardSheetViewQueryHandler;

    /**
     * DashboardViewQueryHandler constructor.
     *
     * @param DashboardTransactionViewQueryHandler $dashboardTransactionViewQueryHandler
     * @param DashboardSheetViewQueryHandler       $dashboardSheetViewQueryHandler
     */
    public function __construct(
        DashboardTransactionViewQueryHandler $dashboardTransactionViewQueryHandler,
        DashboardSheetViewQueryHandler $dashboardSheetViewQueryHandler
    ) {
        $this->dashboardTransactionViewQueryHandler = $dashboardTransactionViewQueryHandler;
        $this->dashboardSheetViewQueryHandler       = $dashboardSheetViewQueryHandler;
    }

    /**
     * @param DashboardViewQuery $dashboardViewQuery
     *
     * @return DashboardView
     */
    public function handle(DashboardViewQuery $dashboardViewQuery)
    {
        $dashboardTransactionView = $this->dashboardTransactionViewQueryHandler->handle(
            new DashboardTransactionViewQuery($dashboardViewQuery->event)
        );

        $dashboardSheetView = $this->dashboardSheetViewQueryHandler->handle(
            new DashboardSheetViewQuery($dashboardViewQuery->event, $dashboardViewQuery->locale)
        );

        return new DashboardView($dashboardTransactionView, $dashboardSheetView);
    }
}
