<?php

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

    /** @var DashboardContactViewQueryHandler */
    private $dashboardContactViewQueryHandler;

    /** @var DashboardScanViewQueryHandler */
    private $dashboardScanViewQueryHandler;

    public function __construct(
        DashboardTransactionViewQueryHandler $dashboardTransactionViewQueryHandler,
        DashboardSheetViewQueryHandler $dashboardSheetViewQueryHandler,
        DashboardMeetingViewQueryHandler $dashboardMeetingViewQueryHandler,
        DashboardContactViewQueryHandler $dashboardContactViewQueryHandler,
        DashboardScanViewQueryHandler $dashboardScanViewQueryHandler
    ) {
        $this->dashboardTransactionViewQueryHandler = $dashboardTransactionViewQueryHandler;
        $this->dashboardSheetViewQueryHandler = $dashboardSheetViewQueryHandler;
        $this->dashboardMeetingViewQueryHandler = $dashboardMeetingViewQueryHandler;
        $this->dashboardContactViewQueryHandler = $dashboardContactViewQueryHandler;
        $this->dashboardScanViewQueryHandler = $dashboardScanViewQueryHandler;
    }

    /**
     * @param DashboardViewQuery $dashboardViewQuery
     *
     * @return DashboardView
     */
    public function handle(DashboardViewQuery $dashboardViewQuery): DashboardView
    {
        return new DashboardView(
            $this->dashboardTransactionViewQueryHandler->handle(
                new DashboardTransactionViewQuery($dashboardViewQuery->event)
            ),
            $this->dashboardSheetViewQueryHandler->handle(
                new DashboardSheetViewQuery($dashboardViewQuery->event, $dashboardViewQuery->locale)
            ),
            $this->dashboardMeetingViewQueryHandler->handle(
                new DashboardMeetingViewQuery($dashboardViewQuery->event)
            ),
            $this->dashboardContactViewQueryHandler->handle(
                new DashboardContactViewQuery($dashboardViewQuery->event)
            ),
            $this->dashboardScanViewQueryHandler->handle(
                new DashboardScanViewQuery($dashboardViewQuery->event)
            )
        );
    }
}
