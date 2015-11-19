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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
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
    public function delete(Participant $participant)
    {
        $this->entityManager->remove($participant);
        $this->entityManager->flush($participant);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Participant $participant)
    {
        $this->entityManager->flush($participant);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from('Entity:Participant', 'participant')
            ->where('participant.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantViewsBySheet($sheetId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\ParticipantView(participant.id, participant.data, user.id, user.email, participant.owner)')
            ->from('Entity:Participant', 'participant')
            ->join('participant.user', 'user')
            ->where('participant.sheet = :sheetId')
            ->setParameter('sheetId', $sheetId);

        return $queryBuilder->getQuery()->getResult();
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
            ->join('participant.sheet', 'sheet')
            ->join('sheet.type', 'type')
            ->join('type.event', 'event', 'WITH', 'event.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('participant.id', 'DESC')
            ->setMaxResults(1);

        $result = $queryBuilder->getQuery()->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result ? intval($result) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantForUserAndSheet(User $user, Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from('Entity:Participant', 'participant')
            ->join('participant.user', 'user', 'WITH', 'user.id = :userId')
            ->setParameter('userId', $user->getId())
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.id = :sheetId')
            ->setParameter('sheetId', $sheet->getId())
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllParticipantForUser($userId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant.id')
            ->from('Entity:Participant', 'participant')
            ->join('participant.user', 'user', 'WITH', 'user.id = :userId')
            ->setParameter('userId', $userId);

        return $queryBuilder->getQuery()->getResult();
    }
}
