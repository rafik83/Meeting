<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Filter;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\FilledTemplateFilter;
use Proximum\Vimeet\Domain\Repository\Filter\FilledTemplateFilterRepositoryInterface;

class FilledTemplateFilterRepository implements FilledTemplateFilterRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getByEvent(Event $event): ?array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('filledTemplateFilter')
            ->from(FilledTemplateFilter::class, 'filledTemplateFilter')
            ->where('filledTemplateFilter.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    public function deleteForEvent(Event $event): void
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(FilledTemplateFilter::class, 'filledTemplateFilter')
            ->where('filledTemplateFilter.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->execute();
    }

    public function add(FilledTemplateFilter $filledTemplateFilter): void
    {
        $this->entityManager->persist($filledTemplateFilter);
        $this->entityManager->flush($filledTemplateFilter);
    }
}
