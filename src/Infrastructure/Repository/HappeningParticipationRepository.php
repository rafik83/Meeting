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
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class HappeningParticipationRepository implements HappeningParticipationRepositoryInterface
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
    public function add(HappeningParticipation $happeningParticipation)
    {
        $this->entityManager->persist($happeningParticipation);
        $this->entityManager->flush($happeningParticipation);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(HappeningParticipation $happeningParticipation)
    {
        $this->entityManager->remove($happeningParticipation);
        $this->entityManager->flush($happeningParticipation);
    }

    /**
     * {@inheritdoc}
     */
    public function findByHappeningAndParticipant(Happening $happening, Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->where('participation.happening = :happening')
            ->setParameter('happening', $happening)
            ->andWhere('participation.participant = :participant')
            ->setParameter('participant', $participant)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening', 'happening')
            ->where('participation.participant = :participant')
            ->setParameter('participant', $participant)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
