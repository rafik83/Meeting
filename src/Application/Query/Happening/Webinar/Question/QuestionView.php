<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

class QuestionView
{
    /** @var int */
    public $questionId;

    /** @var string */
    public $questionContent;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string|null */
    public $position;

    /** @var string|null */
    public $avatar;

    /** @var string|null */
    public $sheetTitle;

    /** @var string */
    public $createdAt;

    /** @var int */
    public $voteCount;

    /** @var bool */
    public $isLiked;

    /** @var bool */
    public $canVote;

    public function __construct(
        int $questionId,
        string $questionContent,
        string $firstName,
        string $lastName,
        ?string $position,
        ?string $avatar,
        ?string $sheetTitle,
        string $createdAt,
        int $voteCount,
        bool $isLiked,
        bool $canVote
    ) {
        $this->questionId = $questionId;
        $this->questionContent = $questionContent;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->avatar = $avatar;
        $this->sheetTitle = $sheetTitle;
        $this->createdAt = $createdAt;
        $this->voteCount = $voteCount;
        $this->isLiked = $isLiked;
        $this->canVote = $canVote;
    }
}
