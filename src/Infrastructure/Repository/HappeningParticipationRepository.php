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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
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

    /**
     * {@inheritdoc}
     */
    public function checkAnyParticipation(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation.id')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening', 'happening')
            ->where('participation.participant = :participant')
            ->setParameter('participant', $participant)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    /**
     * {@inheritdoc}
     */
    public function countParticipationByHappening(Happening $happening)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participation)')
            ->from(HappeningParticipation::class, 'participation')
            ->where('participation.happening  = :happening')
            ->setParameter('happening', $happening)
        ;

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening', 'happening')
            ->where('happening.event = :event')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countParticipationByEvent(Event $event)
    {
        $participationCounts = [];

        $results = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening.id, COUNT(participation)')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening', 'happening', 'WITH', 'happening = participation.happening')
            ->join('happening.event', 'event', 'WITH', 'event = :event')
            ->setParameter('event', $event)
            ->groupBy('happening.id')
            ->getQuery()
            ->getResult()
        ;

        //  Reformat the array to key (happening id) => value (count participation)
        foreach ($results as $result) {
            $participationCounts[$result['id']] = $result[1];
        }

        return $participationCounts;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipationsForSheet(Sheet $sheet, $happenings)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.participant', 'participant', 'WITH', 'participant.sheet = :sheet')
            ->join('participation.happening', 'happening', 'WITH', 'happening IN (:happenings)')
            ->setParameter('sheet', $sheet)
            ->setParameter('happenings', $happenings)
            ->groupBy('participation.happening, participation.participant')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeParticipantForHappening(Participant $participant, Happening $happening)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(HappeningParticipation::class, 'participation')
            ->where('participation.participant = :participant')
            ->andWhere('participation.happening = :happening')
            ->setParameter('participant', $participant)
            ->setParameter('happening', $happening)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }
}
