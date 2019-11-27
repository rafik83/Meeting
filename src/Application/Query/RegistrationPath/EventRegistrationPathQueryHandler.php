<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

            $answerViews = [];

            foreach ($question->getAnswers() as $answer) {
                $answerId = $answer->getId();
                $typesViews = [];

                foreach ($answer->getTypes() as $type) {
                    $typesViews[] = new TypeView($type->getId(), $type->getTitle($query->locale));
                }

                $answerView = new AnswerView($answerId, $answer->getTitle($query->locale), $typesViews);
                $answerViews[] = $answerView;
                $allAnswerViews[$answerId] = $answerView;
            }

            $questionId = $question->getId();
            $questionView = new QuestionView(
                $questionId,
                $question->getTitle($query->locale),
                $answerViews
            );
            $questionViews[$question->getId()] = $questionView;

            if (null === $previousAnswer) {
                $firstQuestionView = $questionView;
            }
        }

        foreach ($previousAnswerIdByQuestion as $questionId => $answerId) {
            $allAnswerViews[$answerId]->setNextQuestionView($questionViews[$questionId]);
        }

        return new EventRegistrationPathView($firstQuestionView);
    }
}
