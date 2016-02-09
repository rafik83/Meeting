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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotRepository implements SpotRepositoryInterface
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
    public function __construct(EntityManager $entityManager) {
        $this->entityManager  = $entityManager;
    }

    /**
     * @param Spot $spot
     */
    public function add(Spot $spot)
    {
        $this->entityManager->persist($spot);
        $this->entityManager->flush($spot);
    }

    /**
     * @param Spot $spot
     */
    public function set(Spot $spot)
    {
        $this->entityManager->flush($spot);
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSpotFilter(Event $event, array $filter = [])
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event);

        if (!empty($filter)) {
            if ($filter['active'] === 0 or $filter['active'] === 1) {
                $queryBuilder
                    ->andWhere('spot.active = :active')
                    ->setParameter('active', $filter['active']);
            } if ($filter['reference'] !== null && !empty($filter['reference'])) {
                $queryBuilder
                    ->andWhere('spot.reference LIKE :reference')
                    ->setParameter('reference', '%'.$filter['reference'].'%');
            } if ($filter['meetingCapacity'] !== 0 && !empty($filter['meetingCapacity'])) {
                $queryBuilder
                    ->andWhere('spot.meetingCapacity = :meetingCapacity')
                    ->setParameter('meetingCapacity', $filter['meetingCapacity']);
            } if ($filter['seatCapacity'] !== 0 && !empty($filter['seatCapacity'])) {
                $queryBuilder
                    ->andWhere('spot.seatCapacity = :seatCapacity')
                    ->setParameter('seatCapacity', $filter['seatCapacity']);
            } if ($filter['size'] !== 0 && !empty($filter['size'])) {
                $queryBuilder
                    ->andWhere('spot.size = :size')
                    ->setParameter('size', $filter['size']);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
