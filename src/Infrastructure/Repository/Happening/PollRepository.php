<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManagerInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class PollRepository implements PollRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Poll $poll): void
    {
        $this->entityManager->persist($poll);
        $this->entityManager->flush($poll);
    }

    public function delete(Poll $poll): void
    {
        $this->entityManager->remove($poll);
        $this->entityManager->flush($poll);
    }

    public function update(Poll $poll): void
    {
        $this->entityManager->flush($poll);
    }

    public function findByHappening(Happening $happening, ?string $status = null): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('poll')
            ->from(Poll::class, 'poll')
            ->leftJoin('poll.pollChoices', 'choices')
            ->andWhere('poll.happening = :happening')
            ->setParameter('happening', $happening)
            ->addOrderBy('poll.createdAt', 'DESC')
            ->addOrderBy('choices.id')
        ;

        if ($status) {
            $queryBuilder
                ->andWhere('poll.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findById(int $pollId): ?Poll
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('poll')
            ->from(Poll::class, 'poll')
            ->leftJoin('poll.pollChoices', 'choices')
            ->andWhere('poll.id = :id')
            ->setParameter('id', $pollId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
