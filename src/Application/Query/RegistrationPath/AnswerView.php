<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

class AnswerView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var QuestionView */
    public $questionView;

    /** @var QuestionView|null */
    public $nextQuestionView;

    /** @var TypeView[] */
    public $typeViews;

    public function __construct(QuestionView $questionView, int $id, string $title, array $typeViews)
    {
        $this->questionView = $questionView;
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

    public function hasOneType(): bool
    {
        return 1 === count($this->typeViews);
    }

    public function getTypeView(): TypeView
    {
        $typeView = reset($this->typeViews);

        if (!$this->hasOneType() && false !== $typeView) {
            throw new \LogicException('This question has not one type.');
        }

        return $typeView;
    }

    public function hasNextStep(): bool
    {
        return $this->hasTypes() || $this->hasNextQuestion();
    }
}
