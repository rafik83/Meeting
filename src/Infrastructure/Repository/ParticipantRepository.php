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
use Doctrine\ORM\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Meeting;
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
    public function getAllParticipantForUser(Event $event, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant.id')
            ->from('Entity:Participant', 'participant')
            ->join('participant.user', 'user', 'WITH', 'user = :user')
            ->setParameter('user', $user)
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsByUserForEvent($userId, EventInterface $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from('Entity:Participant', 'participant')
            ->join('participant.user', 'user', 'WITH', 'user.id = :userId')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :eventId')
            ->setParameter('userId', $userId)
            ->setParameter('eventId', $event->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getInactiveParticipantForSheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from('Entity:Participant', 'participant')
            ->where('participant.sheet = :sheetId')
            ->setParameter('sheetId', $sheet->getId())
            ->andWhere('participant.active = false');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAvailableBySheetAndMeeting(Sheet $sheet, Meeting $meeting)
    {
        return $this->getAvailableParticipantsForMeeting($sheet->getParticipants()->toArray(), $meeting);
    }

    /**
     * {@inheritdoc}
     */
    public function countByEnabledSheet(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participant)')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.enable = :enable AND sheet.event = :event')
            ->setParameter('event', $event)
            ->setParameter('enable', true);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByTypeWithEnabledSheet(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participant) as total, type.id, typeTranslation.title')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.enable = :enable AND sheet.event = :event')
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->groupBy('type')
            ->setParameter('enable', true)
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableParticipants(
        array $participants,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        Meeting $exceptedMeeting = null,
        Happening $exceptedHappening = null
    ) {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->where('participant IN (:participants)')
            ->setParameter('participants', $participants);

        $queryBuilder->andWhere(
            $queryBuilder->expr()->andX(
                // Participant have not already a meeting during this period
                "NOT EXISTS (
                    SELECT m.id
                    FROM Entity:Meeting m
                    JOIN m.slot slot
                    LEFT JOIN m.fromParticipants fp
                    LEFT JOIN m.toParticipants tp
                    WHERE
                        " . (null !== $exceptedMeeting ? 'm != :exceptedMeeting' : '1=1') . "
                        AND (fp.id = participant OR tp.id = participant)
                        AND (
                            slot.begin BETWEEN :begin AND :end
                            OR slot.end BETWEEN :begin AND :end
                            OR :begin BETWEEN slot.begin AND slot.end
                            OR :end BETWEEN slot.begin AND slot.end
                        )
                )",
                // Participant have not unavailability during this period
                "NOT EXISTS (
                    SELECT u.id
                    FROM Entity:Unavailability u
                    WHERE
                        u.participant = participant
                        AND (
                            u.begin BETWEEN :begin AND :end
                            OR u.end BETWEEN :begin AND :end
                            OR :begin BETWEEN u.begin AND u.end
                            OR :end BETWEEN u.begin AND u.end
                        )
                )",
                // Participant have not happening during this period
                "NOT EXISTS (
                    SELECT hp.id
                    FROM Entity:HappeningParticipation hp
                    JOIN hp.happening h
                    WHERE
                        " . (null !== $exceptedHappening ? 'h != :exceptedHappening' : '1=1') . "
                        AND hp.participant = participant
                        AND (
                            h.begin BETWEEN :begin AND :end
                            OR h.end BETWEEN :begin AND :end
                            OR :begin BETWEEN h.begin AND h.end
                            OR :end BETWEEN h.begin AND h.end
                        )
                )"
            )
        );

        if (null !== $exceptedMeeting) {
            $queryBuilder->setParameter('exceptedMeeting', $exceptedMeeting);
        }

        if (null !== $exceptedHappening) {
            $queryBuilder->setParameter('exceptedHappening', $exceptedHappening);
        }

        $queryBuilder
            ->setParameter('begin', $begin)
            ->setParameter('end', $end);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableParticipantsForMeeting(array $participants, Meeting $meeting) {
        return $this->getAvailableParticipants(
            $participants,
            $meeting->getSlot()->getBegin(),
            $meeting->getSlot()->getEnd(),
            $meeting
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableParticipantsForHappening(array $participants, Happening $happening) {
        return $this->getAvailableParticipants(
            $participants,
            $happening->getBegin(),
            $happening->getEnd(),
            null,
            $happening
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsForHappening(Sheet $sheet, Happening $happening)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join(
                'participant.happeningParticipations',
                'happeningParticipation',
                'WITH',
                'participant.sheet = :sheet AND happeningParticipation.happening = :happening'
            )
            ->setParameter('sheet', $sheet)
            ->setParameter('happening', $happening)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
