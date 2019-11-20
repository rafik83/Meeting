<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\RegistrationPath;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

class QuestionRepository implements QuestionRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Question $question): void
    {
        $this->entityManager->persist($question);
        $this->entityManager->flush($question);
    }
}
