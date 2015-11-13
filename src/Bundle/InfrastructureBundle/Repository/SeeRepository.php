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
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Repository\SeeRepositoryInterface;

class SeeRepository implements SeeRepositoryInterface
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
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('see')
            ->from('Entity:See', 'see')
            ->where('see.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventSeerAndSeeable(Event $event, WhoInterface $seer, WhoInterface $seeable)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('see')
            ->from('Entity:See', 'see')
            ->where('see.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        if ($seer instanceof Type) {
            $queryBuilder
                ->andWhere('see.seerType = :seerType')
                ->setParameter('seerType', $seer);
        } elseif ($seer instanceof Category) {
            $queryBuilder
                ->andWhere('see.seerCategory = :seerCategory')
                ->setParameter('seerCategory', $seer);
        }

        if ($seeable instanceof Type) {
            $queryBuilder
                ->andWhere('see.seeableType = :seeableType')
                ->setParameter('seeableType', $seeable);
        } elseif ($seeable instanceof Category) {
            $queryBuilder
                ->andWhere('see.seeableCategory = :seeableCategory')
                ->setParameter('seeableCategory', $seeable);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(See $see)
    {
        $this->entityManager->persist($see);
        $this->entityManager->flush($see);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(See $see)
    {
        $this->entityManager->remove($see);
        $this->entityManager->flush($see);
    }
}
