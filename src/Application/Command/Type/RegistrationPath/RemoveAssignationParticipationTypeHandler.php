<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Repository\RegistrationPath\AnswerRepositoryInterface;

class RemoveAssignationParticipationTypeHandler
{
    /** @var AnswerRepositoryInterface */
    private $answerRepository;

    public function __construct(AnswerRepositoryInterface $answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    public function handle(RemoveAssignationParticipationType $removeAssignationParticipationType): void
    {
        $answer = $removeAssignationParticipationType->answer;
        $answer->removeTypes();
        $this->answerRepository->set($answer);
    }
}
