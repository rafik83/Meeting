<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\User;

class ReplyHappeningQuestion implements Command
{
    /** @var int */
    public $questionId;

    /** @var User */
    public $repliedBy;

    /** @var string */
    public $replyContent;

    public function __construct(int $questionId, User $repliedBy, string $replyContent)
    {
        $this->questionId = $questionId;
        $this->repliedBy = $repliedBy;
        $this->replyContent = $replyContent;
    }
}
