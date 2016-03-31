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
    public function getLastAcceptBySheet(TraceableInterface $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('trace')
            ->from(Trace::class, 'trace', 'trace.id')
            ->where('trace.object = :object')
            ->setParameter('object', sprintf('%s%s', $sheet->getTraceableName(), $sheet->getId()))
            ->andWhere('trace.action = :action')
            ->setParameter('action', Trace::ACCEPT)
            ->orderBy('trace.date', 'DESC')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
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
