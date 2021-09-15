<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class PackageRepository implements PackageRepositoryInterface
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
    public function add(Package $package)
    {
        $this->entityManager->persist($package);
        $this->entityManager->flush($package);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Package $package)
    {
        $this->entityManager->flush($package);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('package')
            ->from(Package::class, 'package')
            ->where('package.event = :event')
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
            ->select('package')
            ->from(Package::class, 'package')
            ->where('package.event IN (:events)')
            ->setParameter('events', $events);

        return $queryBuilder->getQuery()->getResult();
    }
}
