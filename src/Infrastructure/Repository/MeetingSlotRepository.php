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
    public function findAvailableSlotsByParticipantsIds(
        Event $event,
        array $participantsId,
        $ignoreMeetings = false,
        Meeting $exceptedMeeting = null
    ) {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot')
            ->from(MeetingSlot::class, 'slot')
            ->where('slot.event = :event')
            ->setParameter('event', $event);

        if (!$ignoreMeetings) {
            // Participants have not already a meeting at this slot
            $queryBuilder
                ->andWhere(
                    sprintf(
                        'NOT EXISTS (
                            SELECT m.id FROM Entity:Meeting m
                            LEFT JOIN m.fromParticipants fp
                            LEFT JOIN m.toParticipants tp
                            WHERE m.slot = slot
                            AND m.state = :meetingState
                            AND (fp.id IN (:ids) OR tp.id IN (:ids))
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
                SELECT u.id FROM Entity:Unavailability u
                WHERE u.participant IN (:ids)
                AND (
                    slot.begin = u.begin
                    OR u.end = slot.end
                    OR u.begin < slot.begin AND slot.begin < u.end
                    OR u.begin < slot.end AND slot.end < u.end
                    OR slot.begin < u.begin AND u.begin < slot.end
                )
            )');

        // Participants have not blocking participation
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT hp.id FROM Entity:HappeningParticipation hp
                JOIN hp.happening h
                WHERE hp.participant IN (:ids)
                AND (
                    slot.begin = h.begin
                    OR h.end = slot.end
                    OR h.begin < slot.begin AND slot.begin < h.end
                    OR h.begin < slot.end AND slot.end < h.end
                    OR slot.begin < h.begin AND h.begin < slot.end
                )
            )');

        // No blocking mass unvailabilities during this slot
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT mass.id FROM Entity:Unavailability\Mass mass
                WHERE mass.blocking = true
                AND (
                    slot.begin = mass.begin
                    OR mass.end = slot.end
                    OR mass.begin < slot.begin AND slot.begin < mass.end
                    OR mass.begin < slot.end AND slot.end < mass.end
                    OR slot.begin < mass.begin AND mass.begin < slot.end
                )
            )');

        $queryBuilder->setParameter('ids', $participantsId);

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
