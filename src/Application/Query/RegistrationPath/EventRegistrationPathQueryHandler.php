<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

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
        $questionView = null;

        foreach ($questions as $question) {
            $answerViews = [];

            foreach ($question->getAnswers() as $answer) {
                $answerViews[] = new AnswerView($answer->getId(), $answer->getTitle($query->locale));
            }

            $questionView = new QuestionView($question->getId(), $question->getTitle($query->locale), $answerViews);
        }

        return new EventRegistrationPathView($questionView);
    }
}
