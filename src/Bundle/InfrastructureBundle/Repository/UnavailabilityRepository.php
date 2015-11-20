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
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
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
    public function findByScheduleSheetAndUser(Schedule $schedule, Sheet $sheet, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->join('unavailability.participant', 'participant', 'WITH', 'participant.sheet = :sheet AND participant.user = :user')
            ->setParameter('sheet', $sheet)
            ->setParameter('user', $user)
            ->where('unavailability.schedule = :schedule')
            ->setParameter('schedule', $schedule);

        return $queryBuilder->getQuery()->getResult();
    }
}
