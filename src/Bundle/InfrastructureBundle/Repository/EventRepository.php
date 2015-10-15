<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class EventRepository implements EventRepositoryInterface
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
    public function findByDomain($domain)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from('Entity:Event', 'event')
            ->where('event.domain = :domain')
            ->setParameter('domain', $domain)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
