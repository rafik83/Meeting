<?php

namespace Proximum\Vimeet\Infrastructure\Repository\RegistrationPath;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\AnswerRepositoryInterface;

class AnswerRepository implements AnswerRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function set(Answer $answer)
    {
        $this->entityManager->flush($answer);
    }
}
