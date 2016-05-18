<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\TraceableInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;

class TraceRepository implements TraceRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SpotRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Trace $trace)
    {
        $this->entityManager->persist($trace);
        $this->entityManager->flush($trace);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastByTraceableObjectsAndAction(array $objects, $action)
    {
        $ids = array_map(function (TraceableInterface $object) {
            return Trace::identifier($object);
        }, $objects);

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('trace')
            ->from(Trace::class, 'trace', 'trace.id')
            ->leftJoin(Trace::class, 'trace2', 'WITH', 'trace2.action = trace.action AND trace2.object = trace.object AND trace2.id > trace.id')
            ->where('trace2.id IS NULL')
            ->andWhere('trace.object IN (:ids)')
            ->setParameter('ids', $ids)
            ->andWhere('trace.action = :action')
            ->setParameter('action', $action);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTracesByObject(TraceableInterface $traceable)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('trace')
            ->from(Trace::class, 'trace', 'trace.id')
            ->where('trace.object = :object')
            ->setParameter('object', sprintf('%s%s', $traceable->getTraceableName(), $traceable->getId()));

        return $queryBuilder->getQuery()->getResult();
    }
}
