<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardEntranceScanView;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;

class DashboardScanViewQueryHandler
{
    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    public function __construct(EventOpenAccessChecker $eventOpenAccessChecker, ScanRepositoryInterface $scanRepository)
    {
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->scanRepository = $scanRepository;
    }

    public function handle(DashboardScanViewQuery $query): DashboardEntranceScanView
    {
        if (!$this->eventOpenAccessChecker->allowedToAccess($query->event) || !$query->event->isAccessControlEnabled()) {
            return new DashboardEntranceScanView(false);
        }

        $visitorsByTypeAndDay = [];
        $uniqueVisitorsIndexedByUserId = [];
        $uniqueVisitorsIndexedByTypeAndUserId = [];
        $uniqueVisitorsByType = [];
        $visitorsTotal = 0;
        $formattedDays = [];

        foreach ($query->event->getDays() as $day) {
            $formattedDay = $day->getBegin()->format('Y-m-d');
            $formattedDays[$formattedDay] = $day->getBegin();

            $dashboardUserAndTypeScanViews = $this->scanRepository->getUserCheckinByEventAndDay(
                $query->event,
                $day->getBegin()
            );

            foreach ($dashboardUserAndTypeScanViews as $dashboardUserAndTypeScanView) {
                $typeId = $dashboardUserAndTypeScanView->getSheetTypeId();
                $userId = $dashboardUserAndTypeScanView->getUserId();

                ++$visitorsTotal;
                $uniqueVisitorsIndexedByUserId[$userId] = true;
                $uniqueVisitorsIndexedByTypeAndUserId[$typeId][$userId] = true;

                if (isset($visitorsByTypeAndDay[$typeId][$formattedDay])) {
                    ++$visitorsByTypeAndDay[$typeId][$formattedDay];
                } else {
                    $visitorsByTypeAndDay[$typeId][$formattedDay] = 1;
                }
            }
        }

        foreach (array_keys($uniqueVisitorsIndexedByTypeAndUserId) as $typeId) {
            $uniqueVisitorsByType[$typeId] = count($uniqueVisitorsIndexedByTypeAndUserId[$typeId]);
        }

        $uniqueVisitorsTotal = count($uniqueVisitorsIndexedByUserId);

        return new DashboardEntranceScanView(
            true,
            $formattedDays,
            $visitorsTotal,
            $uniqueVisitorsTotal,
            $uniqueVisitorsByType,
            $visitorsByTypeAndDay
        );
    }
}
