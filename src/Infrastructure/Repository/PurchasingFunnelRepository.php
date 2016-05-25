<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PurchasingFunnel;
use Proximum\Vimeet\Domain\Repository\PurchasingFunnelRepositoryInterface;

class PurchasingFunnelRepository implements PurchasingFunnelRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * OrderRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(PurchasingFunnel $purchasingFunnel)
    {
        $this->entityManager->persist($purchasingFunnel);
        $this->entityManager->flush($purchasingFunnel);
    }

    /**
     * {@inheritdoc}
     */
    public function set(PurchasingFunnel $purchasingFunnel)
    {
        $this->entityManager->flush($purchasingFunnel);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('purchasingFunnel')
            ->from(PurchasingFunnel::class, 'purchasingFunnel')
            ->where('purchasingFunnel.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvents(array $events)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('purchasingFunnel')
            ->from(PurchasingFunnel::class, 'purchasingFunnel')
            ->where('purchasingFunnel.event IN (:events)')
            ->setParameter('events', $events);

        return $queryBuilder->getQuery()->getResult();
    }
}
