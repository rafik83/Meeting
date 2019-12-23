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

    public function hasRegistrationPath(): bool
    {
        return $this->hasQuestion() && $this->isPathCompleted();
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

    public function getQuestionViewById(int $id): ?QuestionView
    {
        return $this->findQuestionViewById($this->questionView, $id);
    }

    private function findQuestionViewById(QuestionView $questionView, int $id): ?QuestionView
    {
        if ($id === $questionView->id) {
            return $questionView;
        }

        foreach ($questionView->answerViews as $answerView) {
            $nextQuestionView = $answerView->nextQuestionView;

            if (null !== $nextQuestionView) {
                if ($id === $nextQuestionView->id) {
                    return $nextQuestionView;
                }

                $foundQuestionView = $this->findQuestionViewById($nextQuestionView, $id);

                if ($foundQuestionView instanceof QuestionView) {
                    return $foundQuestionView;
                }
            }
        }

        return null;
    }

    public function getAnswerViewById(int $id): ?AnswerView
    {
        return $this->findAnswerViewById($this->questionView, $id);
    }

    private function findAnswerViewById(QuestionView $questionView, int $id): ?AnswerView
    {
        foreach ($questionView->answerViews as $answerView) {
            if ($id === $answerView->id) {
                return $answerView;
            }

            $nextQuestionView = $answerView->nextQuestionView;

            if (null !== $nextQuestionView) {
                $foundAnswerView = $this->findAnswerViewById($nextQuestionView, $id);

                if ($foundAnswerView instanceof AnswerView) {
                    return $foundAnswerView;
                }
            }
        }

        return null;
    }
}
