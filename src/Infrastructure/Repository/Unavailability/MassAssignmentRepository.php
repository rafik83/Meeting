<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Unavailability;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;

class MassAssignmentRepository implements MassAssignmentRepositoryInterface
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
    public function add(MassAssignment $massAssignment)
    {
        $this->entityManager->persist($massAssignment);
        $this->entityManager->flush($massAssignment);
        $this->entityManager->detach($massAssignment);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Mass $mass, Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment')
            ->from(MassAssignment::class, 'assignment')
            ->where('assignment.mass = :mass')
            ->andWhere('assignment.participant = :participant')
            ->setParameter('mass', $mass)
            ->setParameter('participant', $participant)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, participant')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.mass', 'mass', 'WITH', 'mass.event = :event')
            ->join('assignment.participant', 'participant')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, participant')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.mass', 'mass', 'WITH', 'mass.event = :event')
            ->join('assignment.participant', 'participant')
            ->where('assignment.enabled = true')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function set(MassAssignment $massAssignment)
    {
        $this->entityManager->flush($massAssignment);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, participant')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.participant', 'participant', 'WITH', 'participant.sheet = :sheet')
            ->join('assignment.mass', 'mass')
            ->setParameter('sheet', $sheet)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.participant', 'participant', 'WITH', 'participant.id = :participant')
            ->join('assignment.mass', 'mass')
            ->setParameter('participant', $participant->getId());
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.participant', 'participant', 'WITH', 'participant.id = :participant')
            ->join('assignment.mass', 'mass', 'WITH', 'assignment.enabled = true')
            ->setParameter('participant', $participant->getId());
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByParticipants(array $participants)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.participant', 'participant', 'WITH', 'participant.id IN (:participants)')
            ->join('assignment.mass', 'mass', 'WITH', 'assignment.enabled = true')
            ->setParameter('participants', $participants);
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
