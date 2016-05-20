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
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('package', 'pi', 'p')
            ->from(Package::class, 'package')
            ->leftJoin('package.productIncluded', 'pi')
            ->leftJoin('pi.product', 'p')
            ->where('package.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
