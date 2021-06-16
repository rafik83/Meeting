<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\User;

class PollVote
{
    private PollChoice $pollChoice;
    private User $user;

    public function __construct(PollChoice $pollChoice, User $user)
    {
        $this->pollChoice = $pollChoice;
        $this->user = $user;
    }

    public function getPollChoice(): PollChoice
    {
        return $this->pollChoice;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
