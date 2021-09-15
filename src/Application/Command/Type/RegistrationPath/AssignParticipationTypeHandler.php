<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Repository\RegistrationPath\AnswerRepositoryInterface;

class AssignParticipationTypeHandler
{
    /** @var AnswerRepositoryInterface */
    private $answerRepository;

    public function __construct(AnswerRepositoryInterface $answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    public function handle(AssignParticipationType $assignParticipationType)
    {
        $answer = $assignParticipationType->answer;
        $answer->setTypes($assignParticipationType->types);
        $this->answerRepository->set($answer);
    }
}
