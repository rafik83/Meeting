<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardEntranceScanView;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;

class DashboardScanViewQueryHandler
{
    /** @var ScanRepositoryInterface */
    private $scanRepository;

    public function __construct(ScanRepositoryInterface $scanRepository)
    {
        $this->scanRepository = $scanRepository;
    }

    public function handle(DashboardScanViewQuery $query): DashboardEntranceScanView
    {
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
            $formattedDays,
            $visitorsTotal,
            $uniqueVisitorsTotal,
            $uniqueVisitorsByType,
            $visitorsByTypeAndDay
        );
    }
}
