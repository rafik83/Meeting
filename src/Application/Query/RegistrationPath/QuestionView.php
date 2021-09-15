<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

class QuestionView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var array */
    public $answerViews;

    /** @var null|AnswerView */
    public $previousAnswerView;

    /**
     * @param int          $id
     * @param string       $title
     * @param AnswerView[] $answerViews
     */
    public function __construct(int $id, string $title, array $answerViews)
    {
        $this->id = $id;
        $this->title = $title;
        $this->answerViews = $answerViews;
    }

    public function setPreviousAnswerView(AnswerView $answerView)
    {
        $this->previousAnswerView = $answerView;
    }
}
