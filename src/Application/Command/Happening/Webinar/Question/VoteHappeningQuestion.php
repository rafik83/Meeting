<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;

class VoteHappeningQuestion implements Command
{
    /** @var int */
    private $questionId;

    /** @var User */
    private $user;

    public function __construct(int $questionId, User $user)
    {
        $this->questionId = $questionId;
        $this->user = $user;
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
