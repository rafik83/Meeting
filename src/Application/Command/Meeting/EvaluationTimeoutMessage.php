<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class EvaluationTimeoutMessage
{
    // delay in seconds after which evaluation is considered as confirmed
    public const WAIT_DELAY = 300;

    private int $meetingId;
    private int $fromUserId;

    public function __construct(Meeting $meeting, User $fromUser)
    {
        $this->meetingId = $meeting->getId();
        $this->fromUserId = $fromUser->getId();
    }

    public function getMeetingId(): int
    {
        return $this->meetingId;
    }

    public function getFromUserId(): int
    {
        return $this->fromUserId;
    }
}
