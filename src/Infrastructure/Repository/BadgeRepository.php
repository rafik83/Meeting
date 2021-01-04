<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;

class BadgeRepository implements BadgeRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Badge $badge): void
    {
        $this->entityManager->persist($badge);
        $this->entityManager->flush($badge);
    }

    public function set(Badge $badge): void
    {
        $this->entityManager->flush($badge);
    }

    public function findByType(Type $type): ?Badge
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('badge')
            ->from(Badge::class, 'badge')
            ->where('badge.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
