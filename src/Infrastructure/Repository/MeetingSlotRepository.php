<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class MeetingSlotRepository implements MeetingSlotRepositoryInterface
{
    private EntityManager $entityManager;
    private TypeRepositoryInterface $typeRepository;
    private DateTimeInterface $dateTime;

    public function __construct(EntityManager $entityManager, TypeRepositoryInterface $typeRepository, DateTimeInterface $dateTime)
    {
        $this->entityManager = $entityManager;
        $this->typeRepository = $typeRepository;
        $this->dateTime = $dateTime;
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
        bool $ignoreMeetings = false,
        Meeting $exceptedMeeting = null,
        bool $excludePastSlots = false
    ): array {
        $userIds = array_map(function (Participant $participant) {
            return $participant->getUser()->getId();
        }, $participants);

        $usersTypes = $this->typeRepository->getTypesByUserIds($event, $userIds);

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

        if ($excludePastSlots) {
            $queryBuilder
                ->andWhere('slot.end > :now')
                ->setParameter('now', $this->dateTime);
        }

        // Participants have not unavailability during this slot
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT unavailability.id FROM Entity:Unavailability unavailability
                WHERE unavailability.user IN (:userIds)
                AND unavailability.event = :event
                AND (
                    slot.begin >= unavailability.begin AND slot.begin < unavailability.end
                    OR slot.end > unavailability.begin AND slot.end <= unavailability.end
                    OR slot.begin <= unavailability.begin AND slot.end >= unavailability.end
                )
            )');

        // Participants have not blocking participation
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT hp.id FROM Entity:HappeningParticipation hp
                JOIN hp.happening happening WITH happening.event = :event AND hp.user IN (:userIds) AND hp.disabled = false
                WHERE
                    slot.begin >= happening.begin AND slot.begin < happening.end
                    OR slot.end > happening.begin AND slot.end <= happening.end
                    OR slot.begin <= happening.begin AND slot.end >= happening.end
            )');

        // No blocking mass unvailabilities during this slot
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT mass.id FROM Entity:Unavailability\Mass mass
                JOIN mass.types massType
                WHERE mass.event = :event
                AND mass.blocking = true
                AND mass.dispatch = false
                AND massType IN (:usersTypes)
                AND (
                    slot.begin >= mass.begin AND slot.begin < mass.end
                    OR slot.end > mass.begin AND slot.end <= mass.end
                    OR slot.begin <= mass.begin AND slot.end >= mass.end
                )
            )');

        // Mass Assignment not blocking
        $queryBuilder
            ->andWhere('NOT EXISTS (
                SELECT assignment.id FROM Entity:Unavailability\MassAssignment assignment
                JOIN assignment.mass massUnavailability
                    WITH assignment.user IN (:userIds) AND massUnavailability.event = :event AND assignment.enabled = true AND massUnavailability.blocking = true
                WHERE
                    slot.begin >= assignment.begin AND slot.begin < assignment.end
                    OR slot.end > assignment.begin AND slot.end <= assignment.end
                    OR slot.begin <= assignment.begin AND slot.end >= assignment.end
            )');

        $queryBuilder
            ->setParameter('userIds', $userIds)
            ->setParameter('usersTypes', $usersTypes)
        ;
        $queryBuilder->addOrderBy('slot.begin', 'ASC');

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

    /**
     * {@inheritdoc}
     */
    public function findWithAtLeastOneMeetingByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot.id')
            ->from(MeetingSlot::class, 'slot', 'slot.id')
            ->where('slot.event = :event')
            ->andWhere('EXISTS(SELECT m.id FROM Entity:Meeting m where m.slot = slot)')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findById($slotId): ?MeetingSlot
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot')
            ->from(MeetingSlot::class, 'slot')
            ->where('slot.id = :slotId')
            ->setParameter('slotId', $slotId)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $slotIds): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot')
            ->from(MeetingSlot::class, 'slot', 'slot.id')
            ->where('slot.id IN (:slotIds)')
            ->setParameter('slotIds', $slotIds)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveSlot(Event $event): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot.id')
            ->from(MeetingSlot::class, 'slot')
            ->where('slot.event = :event AND slot.locked = false')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findSlotIdsByEvents(array $events): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot.id')
            ->from(MeetingSlot::class, 'slot', 'slot.id')
            ->where('slot.event IN (:events)')
            ->setParameter('events', $events)
        ;

        return array_keys($queryBuilder->getQuery()->getResult());
    }
}
