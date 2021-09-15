<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\User;

class PollChoicesNotRecognizedException extends \LogicException
{
    private Poll $poll;
    private User $user;
    private array $choicesId;

    public function __construct(Poll $poll, User $user, array $choicesId)
    {
        parent::__construct(
            sprintf(
                'user %s submitted many choices on poll %s but some were not recognized. Choices : %s',
                $user->getId(),
                $poll->getId(),
                implode(', ', $choicesId)
            )
        );
        $this->poll = $poll;
        $this->user = $user;
        $this->choicesId = $choicesId;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChoicesId(): array
    {
        return $this->choicesId;
    }
}
