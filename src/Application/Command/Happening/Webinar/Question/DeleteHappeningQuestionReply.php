<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\User;

class DeleteHappeningQuestionReply implements Command
{
    /** @var int */
    public $questionId;

    /** @var User */
    public $user;

    public function __construct(int $questionId, User $user)
    {
        $this->questionId = $questionId;
        $this->user = $user;
    }
}
