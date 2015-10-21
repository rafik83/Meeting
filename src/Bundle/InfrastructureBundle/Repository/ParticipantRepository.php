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
use Doctrine\ORM\Query;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantRepository implements ParticipantRepositoryInterface
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
    public function add(Participant $participant)
    {
        $this->entityManager->persist($participant);
        $this->entityManager->flush($participant);
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantView($participantId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\Model\ParticipantView(participant.data, user.email, event.id, event.title, type.id, typeTranslation.title)')
            ->from('Entity:Participant', 'participant')
            ->join('participant.user', 'user')
            ->join('participant.event', 'event')
            ->join('participant.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('participant.id = :participantId')
            ->setParameter('participantId', $participantId)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastParticipantIdForEventAndUser($userId, $eventId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant.id')
            ->from('Entity:Participant', 'participant')
            ->join('participant.user', 'user', 'WITH', 'user.id = :userId')
            ->setParameter('userId', $userId)
            ->join('participant.event', 'event', 'WITH', 'event.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('participant.id', 'DESC')
            ->setMaxResults(1);

        $result = $queryBuilder->getQuery()->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result ? intval($result) : null;
    }
}
