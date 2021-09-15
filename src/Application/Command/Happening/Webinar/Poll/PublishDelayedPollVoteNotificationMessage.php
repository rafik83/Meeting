<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Domain\Model\Happening\Poll;

class PublishDelayedPollVoteNotificationMessage
{
    /** @var int delay in seconds */
    public const WAIT_DELAY = 1;
    private int $pollId;

    public function __construct(Poll $poll)
    {
        $this->pollId = $poll->getId();
    }

    public function getPollId(): int
    {
        return $this->pollId;
    }
}
