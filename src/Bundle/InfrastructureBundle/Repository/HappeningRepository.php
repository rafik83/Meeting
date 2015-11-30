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
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningRepository implements HappeningRepositoryInterface
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
    public function findByScheduleAndParticipant(Schedule $schedule, Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening')
            ->from(Happening::class, 'happening')
            ->join(HappeningParticipation::class, 'parcipation', 'WITH', 'parcipation.happening = happening AND parcipation.participant = :participant')
            ->setParameter('participant', $participant)
            ->where('happening.schedule = :schedule')
            ->setParameter('schedule', $schedule);

        return $queryBuilder->getQuery()->getResult();
    }
}
