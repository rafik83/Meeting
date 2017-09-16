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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;

class PlannerJobRepository implements PlannerJobRepositoryInterface
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
    public function add(PlannerJob $plannerJob): void
    {
        $this->entityManager->persist($plannerJob);
        $this->entityManager->flush($plannerJob);
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('plannerJob, admin')
            ->from(PlannerJob::class, 'plannerJob')
            ->join('plannerJob.admin', 'admin', 'WITH', 'plannerJob.event = :event AND plannerJob.status=:status')
            ->setParameter('event', $event)
            ->setParameter('status', PlannerJob::STATUS_PENDING)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
