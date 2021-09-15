<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Spot\FilteredQueryBuilder;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Spot\SpotQueryBuilder;

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
        $this->entityManager = $entityManager;
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
    public function find(Event $event, $id, $visio = false)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->andWhere('spot.id = :id')
            ->setParameter('event', $event)
            ->setParameter('id', $id)
            ->setMaxResults(1);

        if (true === $visio) {
            $queryBuilder
                ->andWhere('spot.visio = :visio')
                ->setParameter('visio', $visio);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findMany(Event $event, array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event);

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in('spot.id', $ids)
        );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveByEvent(Event $event): array
    {
        $queryBuilder = new FilteredQueryBuilder($this->entityManager);
        $queryBuilder->hasEvent($event)->filter(['active' => true]);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveSpot(Event $event): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot.id')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event AND spot.active = true')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
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
            ->select('spot')
            ->from(Spot::class, 'spot', 'spot.id')
            ->where('spot.id IN (:spotsIds)')
            ->setParameter('spotsIds', $spotsIds)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBatchSpot(array $spots, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event)
            ->andWhere('spot.id IN (:ids)')
            ->setParameter('ids', array_map(function (Spot $spot) {
                return $spot->getId();
            }, $spots)
            );

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
            ->setParameter('ids', $ids);

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
            ->setParameter('ids', $ids);

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getSpotsForMeeting(Meeting $meeting, $visio = false)
    {
        return $this->getSpotsForSlotAndParticipantsQuantity(
            $meeting->getSlot(),
            $meeting->countParticipants(),
            $meeting,
            null,
            null,
            $visio
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasSpotsForMeeting(Meeting $meeting, $visio = false)
    {
        return $this->hasSpotsForSlotAndParticipantsQuantity(
            $meeting->getSlot(),
            $meeting->countParticipants(),
            $meeting,
            null,
            null,
            $visio
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasMeeting(Spot $spot)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.spot = :spot_id')
            ->setParameter('spot_id', $spot->getId())
            ->setMaxResults(1)
        ;

        return 0 === (int) $queryBuilder->getQuery()->getSingleScalarResult() ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpotsForSlotAndParticipantsQuantity(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null,
        Sheet $fromSheet = null,
        Sheet $toSheet = null,
        $visio = false
    ) {
        $queryBuilder = $this->getSpotsForSlotAndParticipantsQuantityQueryBuilder(
            $slot,
            $participantsQuantity,
            $exceptMeeting,
            $fromSheet,
            $toSheet,
            $visio
        );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSpotsForSlotAndParticipantsQuantity(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null,
        Sheet $fromSheet = null,
        Sheet $toSheet = null,
        $visio = false
    ) {
        $queryBuilder = $this->getSpotsForSlotAndParticipantsQuantityQueryBuilder(
            $slot,
            $participantsQuantity,
            $exceptMeeting,
            $fromSheet,
            $toSheet,
            $visio
        );

        $queryBuilder->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param MeetingSlot $slot
     * @param $participantsQuantity
     * @param Meeting|null $exceptMeeting
     * @param Sheet|null   $fromSheet
     * @param Sheet|null   $toSheet
     * @param bool         $visio
     *
     * @return SpotQueryBuilder
     */
    private function getSpotsForSlotAndParticipantsQuantityQueryBuilder(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null,
        Sheet $fromSheet = null,
        Sheet $toSheet = null,
        $visio = false
    ) {
        $queryBuilder = new SpotQueryBuilder($this->entityManager);

        $queryBuilder
            ->filterByEvent($slot->getEvent())
            ->active()
            ->addSelect('COUNT(meeting.id) AS HIDDEN countMeetings')
            ->addSelect('COUNT(fromParticipant.id) + COUNT(toParticipant.id) AS HIDDEN countParticipants');

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
                ->setParameter('exceptMeeting', $exceptMeeting)
            ;

            if (null === $fromSheet && null === $toSheet) {
                $fromSheet = $exceptMeeting->getFromSheet();
                $toSheet   = $exceptMeeting->getToSheet();
            }
        }

        if (null !== $fromSheet && null !== $toSheet) {
            $queryBuilder->meetingSheets($fromSheet, $toSheet);
        }

        $queryBuilder
            ->visio($visio)
            ->hasNotSpotUnavailability($slot)
            ->addOrderBy('spot.reference')
        ;

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function findSharedByEvent(Event $event): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('spot, unavailabilities')
            ->from(Spot::class, 'spot')
            ->leftJoin('spot.spotUnavailabilities', 'unavailabilities', 'with', 'spot.event = :event')
            ->leftJoin('spot.sheets', 'sheet', 'with', 'spot.event = :event')
            ->where('spot.event = :event')
            ->andWhere('sheet.id IS NULL')
            ->andWhere('spot.active = TRUE')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
