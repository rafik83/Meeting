<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class EvaluationTimeoutMessage
{
    // delay in seconds after which evaluation is considered as confirmed
    public const WAIT_DELAY = 300;

    private int $meetingId;
    private int $fromUserId;
    /** @var int[] $contactIds */
    private array $contactIds;

    /** @param Contact[] $contacts */
    public function __construct(Meeting $meeting, User $fromUser, array $contacts)
    {
        $this->meetingId = $meeting->getId();
        $this->fromUserId = $fromUser->getId();
        $this->contactIds = array_map(fn (Contact $c) => $c->getContact()->getId(), $contacts);
    }

    public function getMeetingId(): int
    {
        return $this->meetingId;
    }

    public function getFromUserId(): int
    {
        return $this->fromUserId;
    }

    /** @return int[] */
    public function getContactIds(): array
    {
        return $this->contactIds;
    }
}
