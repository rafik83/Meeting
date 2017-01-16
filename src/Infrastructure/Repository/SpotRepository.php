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
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Spot\FilteredQueryBuilder;

class SpotRepository implements SpotRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SpotRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Spot $spot)
    {
        $this->entityManager->persist($spot);
        $this->entityManager->flush($spot);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Spot $spot)
    {
        $this->entityManager->flush($spot);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Event $event, $id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event)
            ->andWhere('spot.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference(Event $event, $reference)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event)
            ->andWhere('spot.reference = :reference')
            ->setParameter('reference', $reference)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSpotFilter(Event $event, array $filter = [])
    {
        $queryBuilder = new FilteredQueryBuilder($this->entityManager);
        $queryBuilder->hasEvent($event)->filter($filter);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBatchSpot(array $ids, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event)
            ->andWhere('spot.id IN (:ids)')
            ->setParameter('ids', $ids)
            ;

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function disableBatchSpot(array $ids, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->update(Spot::class, 'spot')
            ->set('spot.active', 'FALSE')
            ->where('spot.event = :event')
            ->setParameter('event', $event)
            ->andWhere('spot.id IN (:ids)')
            ->setParameter('ids', $ids)
        ;

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function enableBatchSpot(array $ids, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->update(Spot::class, 'spot')
            ->set('spot.active', 'TRUE')
            ->where('spot.event = :event')
            ->setParameter('event', $event)
            ->andWhere('spot.id IN (:ids)')
            ->setParameter('ids', $ids)
        ;

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getSpotsForMeeting(Meeting $meeting)
    {
        return $this->getSpotsForSlotAndParticipantsQuantity(
            $meeting->getSlot(),
            $meeting->countParticipants(),
            $meeting
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSpotsForSlotAndParticipantsQuantity(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null
    ) {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->addSelect('COUNT(meeting.id) AS HIDDEN countMeetings')
            ->addSelect('COUNT(fromParticipant.id) + COUNT(toParticipant.id) AS HIDDEN countParticipants')
            ->from(Spot::class, 'spot')
            ->leftJoin(
                Meeting::class,
                'meeting',
                'WITH',
                sprintf(
                    'meeting.spot = spot AND meeting.state = :state AND meeting.slot = :slot %s',
                    null !== $exceptMeeting ? 'AND meeting != :exceptMeeting' : ''
                )
            )
            ->setParameter('exceptMeeting', $exceptMeeting)
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setParameter('slot', $slot)
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->andWhere('spot.active = true')
            ->groupBy('spot.id')
            ->andHaving('countMeetings < spot.meetingCapacity')
            ->andHaving('(countParticipants + :participantsQuantity) <= spot.seatCapacity')
            ->setParameter('participantsQuantity', $participantsQuantity);

        return $queryBuilder->getQuery()->getResult();
    }
}
