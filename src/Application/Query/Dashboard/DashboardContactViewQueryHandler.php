<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardContactView;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class DashboardContactViewQueryHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    public function __construct(MeetingRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
    }

    public function handle(DashboardContactViewQuery $query): DashboardContactView
    {
        $dashboardMeetingContactEvaluationViews = $this->meetingRepository->getDashboardMeetingContactEvaluationViews($query->event);

        $evaluationIndexedByTypeId = [];
        $meetingEvaluatedIndexedById = [];
        $meetingNotEvaluatedIndexedById = [];

        foreach ($dashboardMeetingContactEvaluationViews as $dashboardMeetingContactEvaluationView) {
            $evaluation = $dashboardMeetingContactEvaluationView->getEvaluation();
            $meetingId = $dashboardMeetingContactEvaluationView->getMeetingId();
            $fromTypeId = $dashboardMeetingContactEvaluationView->getFromTypeId();

            if (null === $evaluation) {
                if (!isset($meetingEvaluatedIndexedById[$meetingId])) {
                    $meetingNotEvaluatedIndexedById[$meetingId] = $fromTypeId;
                }

                continue;
            }

            $meetingEvaluatedIndexedById[$meetingId] = true;

            if (isset($meetingNotEvaluatedIndexedById[$meetingId])) {
                unset($meetingNotEvaluatedIndexedById[$meetingId]);
            }

            if (!isset($evaluationIndexedByTypeId[$fromTypeId][$evaluation])) {
                $evaluationIndexedByTypeId[$fromTypeId][$evaluation] = 1;
            } else {
                ++$evaluationIndexedByTypeId[$fromTypeId][$evaluation];
            }
        }

        $meetingNotEvaluatedIndexedByFromTypeId = [];

        foreach ($meetingNotEvaluatedIndexedById as $meetingId => $fromTypeId) {
            if (!isset($meetingNotEvaluatedIndexedByFromTypeId[$fromTypeId])) {
                $meetingNotEvaluatedIndexedByFromTypeId[$fromTypeId] = 1;
            } else {
                ++$meetingNotEvaluatedIndexedByFromTypeId[$fromTypeId];
            }
        }

        return new DashboardContactView($evaluationIndexedByTypeId, $meetingNotEvaluatedIndexedByFromTypeId);
    }
}
