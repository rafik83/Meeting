<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

class AnswerView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var QuestionView|null */
    public $nextQuestionView;

    public function __construct(int $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    public function setNextQuestionView(QuestionView $nextQuestionView): void
    {
        $this->nextQuestionView = $nextQuestionView;
    }
}
