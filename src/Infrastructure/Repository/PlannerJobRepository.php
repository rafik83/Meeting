<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Admin;
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
    public function set(PlannerJob $plannerJob): void
    {
        $this->entityManager->flush($plannerJob);
    }

    /**
     * {@inheritdoc}
     */
    public function findLastByEvent(Event $event): ?PlannerJob
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('plannerJob, admin')
            ->from(PlannerJob::class, 'plannerJob')
            ->join('plannerJob.admin', 'admin', 'WITH', 'plannerJob.event = :event')
            ->orderBy('plannerJob.createdAt', 'desc')
            ->setParameter('event', $event)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function countByAdmin(Admin $admin): int
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('count(plannerJob.id)')
            ->from(PlannerJob::class, 'plannerJob')
            ->where('plannerJob.admin = :admin')
            ->setParameter('admin', $admin->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $id): ?PlannerJob
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('plannerJob')
            ->from(PlannerJob::class, 'plannerJob')
            ->where('plannerJob.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByFilename(string $filename): ?PlannerJob
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('plannerJob')
            ->from(PlannerJob::class, 'plannerJob')
            ->join('plannerJob.file', 'file', 'WITH', 'file.path = :filename')
            ->setParameter('filename', $filename)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
