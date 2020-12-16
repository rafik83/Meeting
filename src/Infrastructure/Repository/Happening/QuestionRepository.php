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
use Proximum\Vimeet\Domain\Model\Happening\QuestionVote;
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

    public function add(Question $question): void
    {
        $this->entityManager->persist($question);
        $this->entityManager->flush($question);
    }

    public function delete(Question $question): void
    {
        $this->entityManager->remove($question);
        $this->entityManager->flush($question);
    }

    public function update(Question $question): void
    {
        $this->entityManager->flush($question);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUserAndHappening(User $user, Happening $happening): array
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

        return $queryBuilder->getQuery()->getResult();
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
    public function findByHappeningAndSheet(Happening $happening, Sheet $sheet): array
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
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByHappeningDuringWebinar(Happening $happening, User $currentUser): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('question')
            ->addSelect('COUNT(vote)')
            ->addSelect('COUNT(DISTINCT userVote.user)')
            ->from(Question::class, 'question')
            ->leftJoin(QuestionVote::class, 'vote', 'WITH', 'vote.question = question')
            ->leftJoin(QuestionVote::class, 'userVote', 'WITH', 'userVote.question = question AND userVote.user = :user')
            ->setParameter('user', $currentUser)
            ->where('question.happening = :happening')
            ->setParameter('happening', $happening)
            ->andWhere('question.askedDuringWebinar = true')
            ->groupBy('question.id')
            ->orderBy('question.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findById(int $id): ?Question
    {
        return $this->entityManager->find(Question::class, $id);
    }

    public function getMessagesCountDuringHappening(Happening $happening): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            // ->select('COUNT(question.id)')
            ->select('SUM(CASE WHEN question.replyContent IS NULL THEN 1 ELSE 2 END)')
            ->from(Question::class, 'question')
            ->where('question.happening = :happening')
            ->andWhere('question.askedDuringWebinar = true')
            ->setParameter('happening', $happening)
        ;

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
