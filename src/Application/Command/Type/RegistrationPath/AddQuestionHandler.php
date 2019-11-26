<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

class AddQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(AddQuestion $addQuestion)
    {
        // @todo: check if previousAnswer has already a question or type de participations

        $question = new Question($addQuestion->event, $addQuestion->previousAnswer);

        foreach ($addQuestion->event->getLocales() as $locale) {
            $question->translate($locale, $addQuestion->translatedTitle[$locale] ?? '');
        }

        $question->setAnswers($addQuestion->answers);

        $this->questionRepository->add($question);
    }
}
