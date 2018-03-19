<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
