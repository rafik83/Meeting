<?php

namespace Proximum\Vimeet\Application\Query\Dashboard\View;

class DashboardContactView
{
    /** @var array */
    private $evaluationIndexedByTypeId;

    /** @var array */
    private $meetingNotEvaluatedIndexedByFromTypeId;

    /**
     * @param array $evaluationIndexedByTypeId
     * @param array $meetingNotEvaluatedIndexedByFromTypeId
     */
    public function __construct(array $evaluationIndexedByTypeId, array $meetingNotEvaluatedIndexedByFromTypeId)
    {
        $this->evaluationIndexedByTypeId = $evaluationIndexedByTypeId;
        $this->meetingNotEvaluatedIndexedByFromTypeId = $meetingNotEvaluatedIndexedByFromTypeId;
    }

    public function getEvaluationIndexedByTypeId(): array
    {
        return $this->evaluationIndexedByTypeId;
    }

    public function getMeetingNotEvaluatedIndexedByFromTypeId(): array
    {
        return $this->meetingNotEvaluatedIndexedByFromTypeId;
    }

    public function getTotalByEvaluation(int $evaluation): int
    {
        $total = 0;

        foreach ($this->evaluationIndexedByTypeId as $evaluationByTypeId) {
            if (isset($evaluationByTypeId[$evaluation])) {
                $total += $evaluationByTypeId[$evaluation];
            }
        }

        return $total;
    }

    public function getTotalNotEvaluated(): int
    {
        $total = 0;

        foreach ($this->meetingNotEvaluatedIndexedByFromTypeId as $meetingNotEvaluatedByTypeId) {
            $total += $meetingNotEvaluatedByTypeId;
        }

        return $total;
    }
}
