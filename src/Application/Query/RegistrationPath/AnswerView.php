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

    /** @var TypeView[] */
    public $typeViews;

    public function __construct(int $id, string $title, array $typeViews)
    {
        $this->id = $id;
        $this->title = $title;
        $this->typeViews = $typeViews;
    }

    public function setNextQuestionView(QuestionView $nextQuestionView): void
    {
        if (null !== $this->nextQuestionView) {
            throw new \LogicException('There is already next question assigned to this answer');
        }

        if (!empty($this->typeViews)) {
            throw new \LogicException('There is already types assigned to this answer');
        }

        $this->nextQuestionView = $nextQuestionView;
    }

    public function hasNextQuestion(): bool
    {
        return null !== $this->nextQuestionView;
    }

    public function hasTypes(): bool
    {
        return !empty($this->typeViews);
    }

    public function hasNextStep(): bool
    {
        return $this->hasTypes() || $this->hasNextQuestion();
    }
}
