<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

class EventRegistrationPathQueryHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(EventRegistrationPathQuery $query): EventRegistrationPathView
    {
        $questions = $this->questionRepository->getQuestionsByEvent($query->event, $query->locale);

        /** @var QuestionView[] $questionViews */
        $questionViews = [];
        $previousAnswerIdByQuestion = [];
        $allAnswerViews = [];
        $firstQuestionView = null;

        foreach ($questions as $question) {
            $previousAnswer = $question->getPreviousAnswer();

            if ($previousAnswer instanceof Answer) {
                $previousAnswerIdByQuestion[$question->getId()] = $previousAnswer->getId();
            }

            $questionId = $question->getId();
            $questionView = new QuestionView(
                $questionId,
                $question->getTitle($query->locale),
                []
            );
            $answerViews = [];

            foreach ($question->getAnswers() as $answer) {
                $answerId = $answer->getId();
                $typesViews = [];

                foreach ($answer->getTypes() as $type) {
                    $typesViews[] = new TypeView($type->getId(), $type->getTitle($query->locale));
                }

                $answerView = new AnswerView($questionView, $answerId, $answer->getTitle($query->locale), $typesViews);
                $answerViews[] = $answerView;
                $allAnswerViews[$answerId] = $answerView;
            }
            $questionView->answerViews = $answerViews;

            $questionViews[$question->getId()] = $questionView;

            if (null === $previousAnswer) {
                $firstQuestionView = $questionView;
            }
        }

        foreach ($previousAnswerIdByQuestion as $questionId => $answerId) {
            $allAnswerViews[$answerId]->setNextQuestionView($questionViews[$questionId]);
            $questionViews[$questionId]->setPreviousAnswerView($allAnswerViews[$answerId]);
        }

        return new EventRegistrationPathView($firstQuestionView);
    }
}
