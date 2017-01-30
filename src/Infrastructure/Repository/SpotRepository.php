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
use Doctrine\ORM\QueryBuilder;
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
    public function getActiveByEvent(Event $event)
    {
        $queryBuilder = new FilteredQueryBuilder($this->entityManager);
        $queryBuilder->hasEvent($event)->filter(['active' => true]);

        return $queryBuilder->getQuery()->getResult();
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
    public function getSpotsByIds(array $spotsIds = [])
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spots')
            ->from(Spot::class, 'spots')
            ->where('spots.id IN (:spotsIds)')
            ->setParameter('spotsIds', $spotsIds)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBatchSpot(array $refIds, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event)
            ->andWhere('spot.reference IN (:refIds)')
            ->setParameter('refIds', $refIds)
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
    public function hasMeeting(Spot $spot) {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.spot)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.spot = :spot_id')
            ->setParameter('spot_id', $spot->getId())
            ->groupBy('meeting.spot')
        ;

        return $queryBuilder->getQuery()->getResult() === 0 ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSpotsForMeeting(Meeting $meeting)
    {
        return $this->hasSpotsForSlotAndParticipantsQuantity(
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
        $queryBuilder = $this->getSpotsForSlotAndParticipantsQuantityQueryBuilder(
            $slot,
            $participantsQuantity,
            $exceptMeeting
        );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSpotsForSlotAndParticipantsQuantity(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null
    ) {
        $queryBuilder = $this->getSpotsForSlotAndParticipantsQuantityQueryBuilder(
            $slot,
            $participantsQuantity,
            $exceptMeeting
        );

        $queryBuilder->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param MeetingSlot  $slot
     * @param              $participantsQuantity
     * @param Meeting|null $exceptMeeting
     *
     * @return QueryBuilder
     */
    private function getSpotsForSlotAndParticipantsQuantityQueryBuilder(
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
            ->where('spot.event = :eventId AND spot.active = true')
            ->setParameter('eventId', $slot->getEvent()->getId());

        // If meeting has a blocked spot, check only meeting's spot availability
        if (null !== $exceptMeeting && $exceptMeeting->isBlockedSpot()) {
            $queryBuilder
                ->andWhere('spot.id = :spotId')
                ->setParameter('spotId', $exceptMeeting->getSpot()->getId());
        }

        // Get meetings and participants count assigned to this spot on current slot
        $queryBuilder
            ->leftJoin(
                Meeting::class,
                'meeting',
                'WITH',
                sprintf(
                    'meeting.spot = spot AND meeting.state = :state AND meeting.slot = :slot %s',
                    // Exclude $exceptMeeting
                    null !== $exceptMeeting ? 'AND meeting != :exceptMeeting' : ''
                )
            )
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setParameter('slot', $slot)
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->groupBy('spot.id')
            ->andHaving('countMeetings < spot.meetingCapacity')
            ->andHaving('(countParticipants + :participantsQuantity) <= spot.seatCapacity')
            ->setParameter('participantsQuantity', $participantsQuantity);

        if (null !== $exceptMeeting) {
            $queryBuilder
                // Get meeting sheets assigned to spot in order to sort Spots list by assigned spots then by shared spots
                ->addSelect('sheetAssignedToSpot.id AS HIDDEN hasSheetAssignedFromMeeting')
                ->leftJoin('spot.sheets', 'sheetAssignedToSpot', 'WITH', 'sheetAssignedToSpot IN (:fromSheetId, :toSheetId)')
                // Exclude spots assigned to others sheet
                ->andWhere('sheetAssignedToSpot IN (:fromSheetId, :toSheetId) OR NOT EXISTS(SELECT sheet.id FROM Entity:Sheet sheet WHERE sheet.spot = spot AND sheet NOT IN (:fromSheetId, :toSheetId))')
                ->setParameter('fromSheetId', $exceptMeeting->getFromSheet()->getId())
                ->setParameter('toSheetId', $exceptMeeting->getToSheet()->getId())
                ->setParameter('exceptMeeting', $exceptMeeting)
                ->addOrderBy('hasSheetAssignedFromMeeting', 'DESC');
        }

        $queryBuilder->addOrderBy('spot.reference');

        return $queryBuilder;
    }
}
