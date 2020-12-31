<?php

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
    public function getLastByTraceableObjectsAndAction(array $objects, $type, $action)
    {
        $ids = array_map(function (TraceableInterface $traceable) {
            return $traceable->getId();
        }, $objects);

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('trace')
            ->from(Trace::class, 'trace', 'trace.id')
            ->leftJoin(Trace::class, 'trace2', 'WITH', 'trace2.action = trace.action AND trace2.objectType = trace.objectType AND trace2.objectId = trace.objectId AND trace2.id > trace.id')
            ->where('trace2.id IS NULL')
            ->andWhere('trace.objectType = :type AND trace.objectId IN (:ids)')
            ->andWhere('trace.action = :action')
            ->setParameter('ids', $ids)
            ->setParameter('type', $type)
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
            ->where('trace.objectType = :type AND trace.objectId = :id')
            ->setParameter('type', $traceable->getTraceableName())
            ->setParameter('id', $traceable->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTracesByObjectAndAction(TraceableInterface $traceable, string $action): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('trace')
            ->from(Trace::class, 'trace', 'trace.id')
            ->where('trace.objectType = :type AND trace.objectId = :id')
            ->andWhere('trace.action = :action')
            ->setParameter('type', $traceable->getTraceableName())
            ->setParameter('id', $traceable->getId())
            ->setParameter('action', $action)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
