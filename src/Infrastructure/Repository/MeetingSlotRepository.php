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
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class MeetingSlotRepository implements MeetingSlotRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * MeetingSlotRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(MeetingSlot $meetingSlot)
    {
        $this->entityManager->persist($meetingSlot);
        $this->entityManager->flush($meetingSlot);
    }

    /**
     * {@inheritdoc}
     */
    public function set(MeetingSlot $meetingSlot)
    {
        $this->entityManager->flush($meetingSlot);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(MeetingSlot $meetingSlot)
    {
        $this->entityManager->remove($meetingSlot);
        $this->entityManager->flush($meetingSlot);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Event $event, $slotId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot')
            ->from(MeetingSlot::class, 'slot')
            ->where('slot.id = :slotId')
            ->andWhere('slot.event = :event')
            ->setParameter('event', $event)
            ->setParameter('slotId', $slotId);

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
            ->select('slot')
            ->from(MeetingSlot::class, 'slot', 'slot.id')
            ->where('slot.event = :event')
            ->setParameter('event', $event)
            ->orderBy('slot.begin', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableSlotByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot')
            ->from(MeetingSlot::class, 'slot', 'slot.id')
            ->where('slot.event = :event')
            ->andWhere('slot.locked = false')
            ->setParameter('event', $event)
            ->orderBy('slot.begin', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(slot.id)')
            ->from(MeetingSlot::class, 'slot', 'slot.id')
            ->where('slot.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAvailableSlotsByParticipants(
        Event $event,
        array $participants,
        $ignoreMeetings = false,
        Meeting $exceptedMeeting = null
    ) {
        $userIds = array_map(function (Participant $participant) {
            return $participant->getUser()->getId();
        }, $participants);

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot')
            ->from(MeetingSlot::class, 'slot')
            ->where('slot.event = :event')
            ->andWhere('slot.locked = false')
            ->setParameter('event', $event);

        if (!$ignoreMeetings) {
            // Participants have not already a meeting at this slot
            $queryBuilder
                ->andWhere(
                    sprintf(
                        'NOT EXISTS (
                            SELECT m.id FROM Entity:Meeting m
                            LEFT JOIN m.fromParticipants fp
                            LEFT JOIN fp.user fpUser
                            LEFT JOIN m.toParticipants tp
                            LEFT JOIN tp.user tpUser
                            WHERE m.slot = slot
                            AND m.state = :meetingState
                            AND (fpUser.id IN (:userIds) OR tpUser.id IN (:userIds))
                            %s
                        )',
                        null !== $exceptedMeeting ? 'AND m != :exceptedMeeting' : ''
                    )
                )
                ->setParameter('meetingState', Meeting::STATE_SCHEDULED);

            if (null !== $exceptedMeeting) {
                $queryBuilder->setParameter('exceptedMeeting', $exceptedMeeting);
            }
        }

        // Participants have not unavailability during this slot
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT unavailability.id FROM Entity:Unavailability unavailability
                WHERE unavailability.user IN (:userIds)
                AND (
                    slot.begin = unavailability.begin
                    OR unavailability.end = slot.end
                    OR unavailability.begin < slot.begin AND slot.begin < unavailability.end
                    OR unavailability.begin < slot.end AND slot.end < unavailability.end
                    OR slot.begin < unavailability.begin AND unavailability.begin < slot.end
                )
            )');

        // Participants have not blocking participation
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT hp.id FROM Entity:HappeningParticipation hp
                JOIN hp.participant hpParticipant
                    WITH hpParticipant.user IN (:userIds)
                JOIN hp.happening happening
                WHERE
                    slot.begin = happening.begin
                    OR happening.end = slot.end
                    OR happening.begin < slot.begin AND slot.begin < happening.end
                    OR happening.begin < slot.end AND slot.end < happening.end
                    OR slot.begin < happening.begin AND happening.begin < slot.end
            )');

        // No blocking mass unvailabilities during this slot
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT mass.id FROM Entity:Unavailability\Mass mass
                WHERE mass.blocking = true
                AND mass.dispatch = false
                AND (
                    slot.begin = mass.begin
                    OR mass.end = slot.end
                    OR mass.begin < slot.begin AND slot.begin < mass.end
                    OR mass.begin < slot.end AND slot.end < mass.end
                    OR slot.begin < mass.begin AND mass.begin < slot.end
                )
            )');

        // Mass Assignment not blocking
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT assignment.id FROM Entity:Unavailability\MassAssignment assignment
                JOIN assignment.participant assignmentParticipant
                    WITH assignment.enabled = true AND assignmentParticipant.user IN (:userIds)
                JOIN assignment.mass massUnavailability
                    WITH massUnavailability.blocking = true
                WHERE
                    slot.begin = assignment.begin
                    OR assignment.end = slot.end
                    OR assignment.begin < slot.begin AND slot.begin < assignment.end
                    OR assignment.begin < slot.end AND slot.end < assignment.end
                    OR slot.begin < assignment.begin AND assignment.begin < slot.end
            )');

        $queryBuilder
            ->setParameter('userIds', $userIds)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Event $event
     * @param Day   $day
     *
     * @return MeetingSlot[]
     */
    public function findByEventAndDay(Event $event, Day $day)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot')
            ->from(MeetingSlot::class, 'slot')
            ->where('slot.begin >= :beginDate AND slot.end <= :endDate')
            ->andWhere('slot.event = :event')
            ->setParameter('beginDate', $day->getStartTime())
            ->setParameter('endDate', $day->getEndTime())
            ->setParameter('event', $event)
            ->orderBy('slot.begin', 'ASC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
