<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

class EventRegistrationPathView
{
    /** @var QuestionView|null */
    public $questionView;

    public function __construct(?QuestionView $questionView)
    {
        $this->questionView = $questionView;
    }

    public function hasQuestion(): bool
    {
        return null !== $this->questionView;
    }

    public function isPathCompleted(): bool
    {
        if (null === $this->questionView) {
            return true;
        }

        return $this->isAnswersHasNextStep($this->questionView);
    }

    private function isAnswersHasNextStep(QuestionView $questionView): bool
    {
        foreach ($questionView->answerViews as $answerView) {
            if (!$answerView->hasNextStep()) {
                return false;
            }

            $questionView = $answerView->nextQuestionView;

            if (null !== $questionView && !$this->isAnswersHasNextStep($questionView)) {
                return false;
            }
        }

        return true;
    }
}
