<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\User;

class NotAllowedVoteException extends \LogicException
{
    private Poll $poll;
    private User $user;

    public function __construct(Poll $poll, User $user)
    {
        parent::__construct(
            sprintf(
                'user %s submitted multiple choices on poll %s but he is not allowed.',
                $user->getId(),
                $poll->getId()
            )
        );
        $this->poll = $poll;
        $this->user = $user;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
