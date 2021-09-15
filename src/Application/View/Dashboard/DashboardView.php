<?php

namespace Proximum\Vimeet\Application\View\Dashboard;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardContactView;
use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardEntranceScanView;

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

    /** @var DashboardEntranceScanView */
    public $dashboardEntranceScanView;

    public function __construct(
        DashboardTransactionView $transactionView,
        DashboardSheetView $dashboardSheetView,
        DashboardMeetingView $dashboardMeetingView,
        DashboardContactView $dashboardContactView,
        DashboardEntranceScanView $dashboardEntranceScanView
    ) {
        $this->transactionView    = $transactionView;
        $this->dashboardSheetView = $dashboardSheetView;
        $this->dashboardMeetingView = $dashboardMeetingView;
        $this->dashboardContactView = $dashboardContactView;
        $this->dashboardEntranceScanView = $dashboardEntranceScanView;
    }
}
