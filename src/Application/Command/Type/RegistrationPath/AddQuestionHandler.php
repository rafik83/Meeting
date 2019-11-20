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
        $question = new Question($addQuestion->event);

        foreach ($addQuestion->event->getLocales() as $locale) {
            $question->translate($locale, $addQuestion->translatedTitle[$locale] ?? '');
        }

        $this->questionRepository->add($question);
    }
}
