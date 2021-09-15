<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class AvailabilityTimeRangeRepository implements AvailabilityTimeRangeRepositoryInterface
{
    /** @var EntityManager */
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
    public function add(AvailabilityTimeRange $availabilityTimeRange): void
    {
        $this->entityManager->persist($availabilityTimeRange);
        $this->entityManager->flush($availabilityTimeRange);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('availabilityTimeRange')
            ->from(AvailabilityTimeRange::class, 'availabilityTimeRange')
            ->where('availabilityTimeRange.event = :event')
            ->orderBy('availabilityTimeRange.begin')
            ->addOrderBy('availabilityTimeRange.end')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasByEvent(Event $event): bool
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('availabilityTimeRange.id')
            ->from(AvailabilityTimeRange::class, 'availabilityTimeRange')
            ->where('availabilityTimeRange.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
