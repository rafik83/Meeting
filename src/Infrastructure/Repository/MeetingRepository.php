<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardMeetingContactEvaluationView;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\View\MeetingView;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Meeting\MeetingQueryBuilder;

class MeetingRepository implements MeetingRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Paginator */
    private $paginator;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var array|null $scheduledMeetingsCount used to preload results in memory */
    private $scheduledMeetingsCount;

    public function __construct(
        EntityManager $entityManager,
        Paginator $paginator,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->entityManager    = $entityManager;
        $this->paginator        = $paginator;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Meeting $meeting)
    {
        $this->entityManager->persist($meeting);
        $this->entityManager->flush($meeting);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Meeting $meeting)
    {
        $this->entityManager->flush($meeting);
    }

    public function findById(int $id): ?Meeting
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event, $page, $limit, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting')
            ->from(Meeting::class, 'meeting', 'meeting.id')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('meeting.toSheet', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->setParameter('event', $event)
            ->orderBy('meeting.createdAt', 'DESC')
        ;

        $pagination = $this->paginator->paginate($queryBuilder, $page, $limit, 'meeting', 'id');

        $pagination->results = array_map(function (Meeting $meeting) use ($locale) {
            return new MeetingView(
                $meeting->getId(),
                $meeting->getFromSheet()->getId(),
                $meeting->getToSheet()->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($meeting->getFromSheet(), $locale),
                $this->sheetInfoGuesser->guessSheetTitle($meeting->getToSheet(), $locale),
                $meeting->getCreatedAt(),
                $meeting->getSlot()->getBegin(),
                $meeting->getSlot()->getEnd(),
                $meeting->getCreatedType()
            );
        }, $pagination->results);

        return $pagination;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting, fromParticipant, toParticipant, request')
            ->from(Meeting::class, 'meeting', 'meeting.id')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'meeting.event = :event AND meeting.state = :state')
            ->join('meeting.toSheet', 'toSheet')
            ->join('meeting.fromParticipants', 'fromParticipant')
            ->join('meeting.toParticipants', 'toParticipant')
            ->join('meeting.request', 'request')
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getNonBlockedSpotByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting, fromSheet, toSheet, fromSheetSpot, toSheetSpot')
            ->from(Meeting::class, 'meeting', 'meeting.id')
            ->join(
                'meeting.fromSheet',
                'fromSheet',
                'WITH',
                'meeting.event = :event AND meeting.state = :state AND meeting.blockedSpot = false'
            )
            ->join('meeting.toSheet', 'toSheet')
            ->leftJoin('toSheet.spot', 'toSheetSpot')
            ->leftJoin('fromSheet.spot', 'fromSheetSpot')
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCompleteByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('
                 m as meeting,
                 fromParticipant,
                 toParticipant,
                 slot.begin as meetingBegin,
                 slot.end as meetingEnd,
                 spot.reference as spotReference
             ')
            ->from(Meeting::class, 'm', 'm.id')
            ->join('m.fromSheet', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('m.toSheet', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->join('m.fromParticipants', 'fromParticipant')
            ->join('m.toParticipants', 'toParticipant')
            ->join('m.slot', 'slot')
            ->join('m.spot', 'spot')
            ->where('m.state = :state')
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

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
            ->select('meeting, fromSheet, toSheet, spot, slot, request, fromParticipantSelected, toParticipantSelected')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->join('meeting.spot', 'spot')
            ->join('meeting.slot', 'slot')
            ->join('meeting.request', 'request')
            ->leftJoin('meeting.fromParticipants', 'fromParticipantSelected')
            ->leftJoin('meeting.toParticipants', 'toParticipantSelected')
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->where('fromParticipant = :participant OR toParticipant = :participant')
            ->andWhere('meeting.state = :state')
            ->setParameter('participant', $participant)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipants(array $participants)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting, fromSheet, toSheet, spot, slot')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->join('meeting.spot', 'spot')
            ->join('meeting.slot', 'slot')
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->where('fromParticipant.id IN (:participants) OR toParticipant.id IN (:participants)')
            ->setParameter('participants', $participants)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndUsers(Event $event, array $users)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('meeting, slot, fromSheet, toSheet')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'fromSheet.event = :event AND meeting.state = :state')
            ->join('meeting.toSheet', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->join('meeting.slot', 'slot')
            ->join('meeting.fromParticipants', 'fromParticipants')
            ->join('meeting.toParticipants', 'toParticipants')
            ->where('toParticipants.user IN (:users) OR fromParticipants.user IN (:users)')
            ->setParameter('users', $users)
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByUserAndEvent(User $user, Event $event)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('meeting, slot, fromSheet, toSheet')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('meeting.toSheet', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->join('meeting.slot', 'slot')
            ->join('meeting.fromParticipants', 'fromParticipants')
            ->join('meeting.toParticipants', 'toParticipants')
            ->where('toParticipants.user = :user OR fromParticipants.user = :user')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByUserAndEventExceptSheet(User $user, Event $event, Sheet $exceptedSheet)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('meeting, slot', 'fromParticipants', 'toParticipants')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('meeting.toSheet', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->join('meeting.slot', 'slot')
            ->join('meeting.fromParticipants', 'fromParticipants')
            ->join('meeting.toParticipants', 'toParticipants')
            ->where('
                (meeting.toSheet != :exceptedSheet AND toParticipants.user = :user)
                OR (meeting.fromSheet != :exceptedSheet AND fromParticipants.user = :user)'
            )
            ->setParameter('exceptedSheet', $exceptedSheet)
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting, fromSheet, toSheet, spot, slot, request, fromParticipantSelected, toParticipantSelected')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->join('meeting.spot', 'spot')
            ->join('meeting.slot', 'slot')
            ->join('meeting.request', 'request')
            ->join('meeting.fromParticipants', 'fromParticipantSelected')
            ->join('meeting.toParticipants', 'toParticipantSelected')
            ->where('fromSheet = :sheet OR toSheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->where('fromParticipant = :participant OR toParticipant = :participant')
            ->setParameter('participant', $participant)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasMeetingForUserAndEvent(User $user, Event $event): bool
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromParticipants', 'fromParticipants', 'WITH', 'meeting.event = :event AND meeting.state = :state')
            ->join('meeting.toParticipants', 'toParticipants')
            ->where('toParticipants.user = :user OR fromParticipants.user = :user')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasScheduledMeetingByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->where('meeting.event = :event')
            ->andWhere('fromParticipant = :participant OR toParticipant = :participant')
            ->andWhere('meeting.state = :state')
            ->setParameter('event', $participant->getSheet()->getEvent())
            ->setParameter('participant', $participant)
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll(Event $event)
    {
        $meetings = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.slot', 'slot', 'WITH', 'slot.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->execute();

        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Meeting::class, 'meeting')
            ->where('meeting.id IN (:ids)')
            ->setParameter('ids', $meetings)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeMeetingOfSheet(Sheet $sheet)
    {
        $meetings = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->where('fromSheet = :sheet OR toSheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->getQuery()
            ->execute();

        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Meeting::class, 'meeting')
            ->where('meeting.id IN (:ids)')
            ->setParameter('ids', $meetings)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingsFromSheet(Sheet $sheet)
    {
        $queryBuilder = new MeetingQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingsToSheet(Sheet $sheet)
    {
        $queryBuilder = new MeetingQueryBuilder($this->entityManager);

        return $queryBuilder->receivedBy($sheet)->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Meeting $meeting)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Meeting::class, 'meeting')
            ->where('meeting = :meeting')
            ->setParameter('meeting', $meeting)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingsOfSheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->where('fromSheet = :sheet OR toSheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingsOfSheetByIds(array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id) AS countMeetings, sheet.id')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join(
                Meeting::class,
                'meeting',
                'WITH',
                'sheet.id IN (:ids) AND meeting.state = :state AND (meeting.fromSheet = sheet OR meeting.toSheet = sheet)'
            )
            ->groupBy('sheet.id')
            ->setParameter('ids', $ids)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingsOfEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id) AS countMeetings, sheet.id')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join(
                Meeting::class,
                'meeting',
                'WITH',
                'sheet.event = :event AND meeting.state = :state AND (meeting.fromSheet = sheet OR meeting.toSheet = sheet)'
            )
            ->groupBy('sheet.id')
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingBySheets(Event $event, array $sheets): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id) AS countMeetings, sheet.id AS sheetId')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join(
                Meeting::class,
                'meeting',
                'WITH',
                'sheet.id IN (:sheets)
                AND meeting.event = :event
                AND (meeting.fromSheet = sheet OR meeting.toSheet = sheet)
                AND meeting.state = :state
            ')
            ->groupBy('sheet.id')
            ->setParameter('event', $event)
            ->setParameter('sheets', $sheets)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getBySheets(Event $event, array $sheets): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting')
            ->from(Meeting::class, 'meeting')
            ->join(
                Sheet::class,
                'sheet',
                'WITH',
                'sheet.id IN (:sheets)
                AND meeting.event = :event
                AND (meeting.fromSheet = sheet OR meeting.toSheet = sheet)
                AND meeting.state = :state
            ')
            ->setParameter('event', $event)
            ->setParameter('sheets', $sheets)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingsBySpots(array $spots): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id) AS countMeetings, spot.id AS spotId')
            ->from(Spot::class, 'spot', 'spot.id')
            ->join(
                Meeting::class,
                'meeting',
                'WITH',
                'spot.id IN (:spots)
                AND meeting.spot = spot
                AND meeting.state = :state'
            )
            ->groupBy('spot.id')
            ->setParameter('spots', $spots)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingForSpots(array $spots): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.spot IN (:spots)')
            ->andWhere('meeting.state = :state')
            ->setParameter('spots', $spots)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countMeetingForSpotsAndSlot(array $spots, MeetingSlot $meetingSlot): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.slot = :slot')
            ->andWhere('meeting.spot IN (:spots)')
            ->andWhere('meeting.state = :state')
            ->setParameter('slot', $meetingSlot)
            ->setParameter('spots', $spots)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByEvent(Event $event): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.event = :event')
            ->andWhere('meeting.state = :state')
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasMeeting(Event $event): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.event = :event')
            ->andWhere('meeting.state = :state')
            ->setMaxResults(1)
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasMeetingOnSlot(MeetingSlot $meetingSlot)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.slot = :slot')
            ->setParameter('slot', $meetingSlot)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySpotAndSlot(Spot $spot, MeetingSlot $meetingSlot)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.slot = :slot')
            ->andWhere('meeting.spot = :spot')
            ->setParameter('slot', $meetingSlot)
            ->setParameter('spot', $spot);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByMeetingSlot(MeetingSlot $slot): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting, fromSheet, fromParticipants, toSheet, toParticipants, spot')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.fromParticipants', 'fromParticipants')
            ->join('meeting.toSheet', 'toSheet')
            ->join('meeting.toParticipants', 'toParticipants')
            ->join('meeting.spot', 'spot')
            ->where('meeting.slot = :slot')
            ->setParameter('slot', $slot)
            ->orderBy('spot.reference')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySpotWithSheets(Spot $spot)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting', 'fromSheet', 'toSheet', 'slot')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'meeting.spot = :spot')
            ->join('meeting.toSheet', 'toSheet')
            ->join('meeting.slot', 'slot')
            ->setParameter('spot', $spot);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasScheduledMeeting(Sheet $sheet): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->where('fromSheet = :sheet OR toSheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function countBetweenDatesByEventAndType(Event $event, \DateTimeInterface $begin, \DateTimeInterface $end, string $type): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.event = :event')
            ->andWhere('meeting.createdAt BETWEEN :begin AND :end')
            ->andWhere('meeting.state = :state')
            ->andWhere('meeting.createdType = :createdType')
            ->setParameter('createdType', $type)
            ->setParameter('event', $event)
            ->setParameter('begin', $begin)
            ->setParameter('end', $end)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countUpstreamByEventAndType(Event $event, \DateTimeInterface $date, string $type): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.event = :event')
            ->andWhere('meeting.createdAt < :date')
            ->andWhere('meeting.state = :state')
            ->andWhere('meeting.createdType = :createdType')
            ->setParameter('event', $event)
            ->setParameter('date', $date)
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setParameter('createdType', $type);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countCreatedByEventAndType(Event $event, string $type): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting.id)')
            ->from(Meeting::class, 'meeting')
            ->where('meeting.event = :event')
            ->andWhere('meeting.createdType = :createdType')
            ->andWhere('meeting.state = :state')
            ->setParameter('createdType', $type)
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function hasAtLeastOneMeeting(array $sheets, array $othersSheets): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting', 'fromSheet')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet',
                'fromSheet',
                'WITH',
                'fromSheet IN (:sheets) AND meeting.toSheet IN (:othersSheets)
                 OR
                 fromSheet IN (:othersSheets) AND meeting.toSheet IN (:sheets)'
            )
            ->setParameter('sheets', $sheets)
            ->setParameter('othersSheets', $othersSheets)
            ->where('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getMeetingAndParticipantsByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting, fromParticipant, toParticipant')
            ->from(Meeting::class, 'meeting', 'meeting.id')
            ->join(
                'meeting.fromParticipants',
                'fromParticipant',
                'WITH',
                'meeting.event = :event AND meeting.state = :state'
            )
            ->join('meeting.toParticipants', 'toParticipant')
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getDashboardMeetingContactEvaluationViews(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'NEW %s(fromType.id, meeting.id, contact.evaluation)',
                    DashboardMeetingContactEvaluationView::class
                )
            )
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'meeting.event = :event AND meeting.state = :state')
            ->join('fromSheet.type', 'fromType')
            ->join('meeting.fromParticipants', 'fromParticipant')
            ->join('fromParticipant.user', 'fromParticipantUser')
            ->join('meeting.toParticipants', 'toParticipant')
            ->join('toParticipant.user', 'toParticipantUser')
            ->leftJoin(
                Contact::class,
                'contact',
                'WITH',
                'contact.event = :event AND contact.user = fromParticipantUser AND contact.contact = toParticipantUser'
            )
            ->setParameter('event', $event)
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->groupBy('fromType.id, meeting.id, contact.evaluation')
            ->getQuery()
            ->getResult();
    }

    public function getPreviousVisioMeeting(
        Event $event,
        Sheet $sheet,
        Participant $participant,
        \DateTimeInterface $begin
    ): ?Meeting {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'meeting.event = :event AND meeting.state = :state')
            ->join('meeting.toSheet', 'toSheet', 'WITH', 'toSheet.id = :sheet OR fromSheet = :sheet')
            ->join('meeting.spot', 'spot', 'WITH', 'spot.visio = true')
            ->join('meeting.slot', 'slot', 'WITH', 'slot.begin < :begin')
            ->join('meeting.fromParticipants', 'from_participant')
            ->join('meeting.toParticipants', 'to_participant', 'WITH', 'to_participant.id = :participant OR from_participant.id = :participant')
            ->orderBy('slot.begin', 'DESC')
            ->setParameter('event', $event)
            ->setParameter('sheet', $sheet)
            ->setParameter('begin', $begin)
            ->setParameter('participant', $participant)
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function loadParticipantMeetingsCount(array $participantIds): void
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('from_participant.id, COUNT(meeting.id) as nb')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromParticipants', 'from_participant', 'WITH', 'from_participant IN (:participants)')
            ->setParameter('participants', $participantIds)
            ->groupBy('from_participant')
        ;
        $participantsFromMeetingsCount = array_column($queryBuilder->getQuery()->getArrayResult(), 'nb', 'id');

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('to_participants.id, COUNT(meeting.id) as nb')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.toParticipants', 'to_participants', 'WITH', 'to_participants IN (:participants)')
            ->setParameter('participants', $participantIds)
            ->groupBy('to_participants')
        ;

        $this->scheduledMeetingsCount = array_reduce($queryBuilder->getQuery()->getArrayResult(), function ($carry, $row) {
            $carry[$row['id']] = ($carry[$row['id']] ?? 0) + $row['nb'];
            return $carry;
        }, $participantsFromMeetingsCount);
    }

    public function getParticipantMeetingsCount(Participant $participant): int
    {
        if (null === $this->scheduledMeetingsCount) {
            throw new \RuntimeException('Meeting counts not loaded, loadParticipantMeetingsCount should be called before this method');
        }

        return $this->scheduledMeetingsCount[$participant->getId()]??0;
    }

    public function getSheetScheduledMeetingsCount(array $sheetIds): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('from_sheet.id, COUNT(meeting.id) as nb')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'from_sheet')
            ->where('from_sheet.id IN(:sheetIds)')
            ->setParameter('sheetIds', $sheetIds)
            ->groupBy('from_sheet.id')
        ;
        $sheetsFromMeetingsCount = array_column($queryBuilder->getQuery()->getArrayResult(), 'nb', 'id');

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('to_sheet.id, COUNT(meeting.id) as nb')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.toSheet', 'to_sheet')
            ->where('to_sheet.id IN (:sheetIds)')
            ->setParameter('sheetIds', $sheetIds)
            ->groupBy('to_sheet.id')
        ;

        return array_reduce($queryBuilder->getQuery()->getArrayResult(), function ($carry, $row) {
            $carry[$row['id']] = ($carry[$row['id']] ?? 0) + $row['nb'];
            return $carry;
        }, $sheetsFromMeetingsCount);
    }
}
