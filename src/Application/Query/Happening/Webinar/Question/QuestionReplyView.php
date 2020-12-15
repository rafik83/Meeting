<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

class QuestionReplyView
{
    /** @var string */
    public $replyContent;

    /** @var string */
    public $repliedBy;

    /** @var string */
    public $repliedAt;

    /** @var bool */
    public $canUpdate;

    public function __construct(
        string $replyContent,
        string $repliedBy,
        string $repliedAt,
        bool $canUpdate
    ) {
        $this->replyContent = $replyContent;
        $this->repliedBy = $repliedBy;
        $this->repliedAt = $repliedAt;
        $this->canUpdate = $canUpdate;
    }
}
