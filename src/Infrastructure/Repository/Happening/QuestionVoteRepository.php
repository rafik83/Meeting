<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\Happening\QuestionVote;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionVoteRepositoryInterface;

class QuestionVoteRepository implements QuestionVoteRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(QuestionVote $questionVote)
    {
        $this->entityManager->persist($questionVote);
        $this->entityManager->flush();
    }

    public function remove(QuestionVote $questionVote)
    {
        $this->entityManager->remove($questionVote);
        $this->entityManager->flush();
    }

    public function getByQuestionAndUser(Question $question, User $user): ?QuestionVote
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('vote')
            ->from(QuestionVote::class, 'vote')
            ->where('vote.question = :question')
            ->andWhere('vote.user = :user')
            ->setParameter('user', $user)
            ->setParameter('question', $question);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
