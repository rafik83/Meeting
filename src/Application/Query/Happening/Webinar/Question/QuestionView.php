<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

class QuestionView
{
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

    public function __construct(
        string $questionContent,
        string $firstName,
        string $lastName,
        ?string $position,
        ?string $avatar,
        ?string $sheetTitle,
        string $createdAt
    ) {
        $this->questionContent = $questionContent;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->avatar = $avatar;
        $this->sheetTitle = $sheetTitle;
        $this->createdAt = $createdAt;
    }
}
