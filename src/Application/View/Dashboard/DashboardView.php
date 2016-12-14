<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Dashboard;

class DashboardView
{
    /**
     * @var DashboardTransactionView
     */
    public $transactionView;

    /**
     * @var DashboardSheetView
     */
    public $dashboardSheetView;

    /**
     * DashboardView constructor.
     *
     * @param DashboardTransactionView $transactionView
     * @param DashboardSheetView       $dashboardSheetView
     */
    public function __construct(
        DashboardTransactionView $transactionView,
        DashboardSheetView $dashboardSheetView
    ) {
        $this->transactionView    = $transactionView;
        $this->dashboardSheetView = $dashboardSheetView;
    }
}
