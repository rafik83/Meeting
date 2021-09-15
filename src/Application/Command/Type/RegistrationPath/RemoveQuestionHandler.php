<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

class RemoveQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(RemoveQuestion $removeQuestion): void
    {
        $this->questionRepository->remove($removeQuestion->question);
    }
}
