<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\User;

class BeginReplyHappeningQuestion implements Command
{
    /** @var int */
    public $questionId;

    /** @var User */
    public $repliedBy;

    public function __construct(int $questionId, User $repliedBy)
    {
        $this->questionId = $questionId;
        $this->repliedBy = $repliedBy;
    }
}
