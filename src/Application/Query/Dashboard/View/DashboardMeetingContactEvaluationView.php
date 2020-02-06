<?php

namespace Proximum\Vimeet\Application\Query\Dashboard\View;

class DashboardMeetingContactEvaluationView
{
    /** @var int */
    private $fromTypeId;

    /** @var int */
    private $meetingId;

    /** @var int|null */
    private $evaluation;

    public function __construct(int $fromTypeId, int $meetingId, ?int $evaluation)
    {
        $this->fromTypeId = $fromTypeId;
        $this->meetingId = $meetingId;
        $this->evaluation = $evaluation;
    }

    public function getFromTypeId(): int
    {
        return $this->fromTypeId;
    }

    public function getMeetingId(): int
    {
        return $this->meetingId;
    }

    public function getEvaluation(): ?int
    {
        return $this->evaluation;
    }
}
