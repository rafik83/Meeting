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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class UnavailabilityRepository implements UnavailabilityRepositoryInterface
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
    public function add(Unavailability $unavailability)
    {
        $this->entityManager->persist($unavailability);
        $this->entityManager->flush($unavailability);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Unavailability $unavailability)
    {
        $this->entityManager->flush($unavailability);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Unavailability $unavailability)
    {
        $this->entityManager->remove($unavailability);
        $this->entityManager->flush($unavailability);
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->where('unavailability.participant = :participant')
            ->setParameter('participant', $participant);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getOverlapUnavailabilities(Unavailability $unavailability)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder();

        $queryBuilder
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->where('unavailability.participant = :participant')
            ->setParameter('participant', $unavailability->getParticipant())
            ->andWhere($queryBuilder->expr()->orX(
                'unavailability.begin BETWEEN :begin AND :end',
                'unavailability.end BETWEEN :begin AND :end',
                ':begin BETWEEN unavailability.begin AND unavailability.end',
                ':end BETWEEN unavailability.begin AND unavailability.end'
            ))
            ->setParameter('begin', $unavailability->getBegin())
            ->setParameter('end', $unavailability->getEnd());

        if ($unavailability->getId()) {
            $queryBuilder
                ->andWhere('unavailability.id != :id')
                ->setParameter('id', $unavailability->getId());
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
