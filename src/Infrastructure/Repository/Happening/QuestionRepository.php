<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class QuestionRepository implements QuestionRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Question $question)
    {
        $this->entityManager->persist($question);
        $this->entityManager->flush($question);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUserAndHappening(User $user, Happening $happening)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('question')
            ->from(Question::class, 'question')
            ->where('question.createdBy = :user')
            ->andWhere('question.happening = :happening')
            ->setParameter('user', $user)
            ->setParameter('happening', $happening);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeQuestionFromUserForHappening(User $createdBy, Happening $happening)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Question::class, 'question')
            ->where('question.createdBy = :createdBy')
            ->andWhere('question.happening = :happening')
            ->setParameter('createdBy', $createdBy)
            ->setParameter('happening', $happening)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByHappeningAndSheet(Happening $happening, Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('question')
            ->from(Question::class, 'question')
            ->where('question.happening = :happening')
            ->andWhere('question.sheet = :sheet')
            ->setParameter('happening', $happening)
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByHappeningDuringWebinar(Happening $happening): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('question')
            ->from(Question::class, 'question')
            ->where('question.happening = :happening')
            ->andWhere('question.askedDuringWebinar = true')
            ->setParameter('happening', $happening)
            ->getQuery()
            ->getResult()
        ;
    }
}
