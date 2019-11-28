<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

class UpdateQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(UpdateQuestion $updateQuestion): void
    {
        $question = $updateQuestion->question;
        $event = $question->getEvent();

        foreach ($event->getLocales() as $locale) {
            $question->translate($locale, $updateQuestion->translatedTitle[$locale] ?? '');
        }

        $question->setAnswers($updateQuestion->answers);

        $this->questionRepository->set($question);
    }
}
