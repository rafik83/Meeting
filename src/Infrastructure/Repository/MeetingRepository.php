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
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
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
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param EntityManager    $entityManager
     * @param Paginator        $paginator
     * @param SheetInfoGuesser $sheetInfoGuesser
     */
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
            ->setParameter('event', $event);

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
                $meeting->getSlot()->getEnd()
            );
        }, $pagination->results);

        return $pagination;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting, fromParticipant, toParticipant, request')
            ->from(Meeting::class, 'meeting', 'meeting.id')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('meeting.toSheet', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->join('meeting.fromParticipants', 'fromParticipant')
            ->join('meeting.toParticipants', 'toParticipant')
            ->join('meeting.request', 'request')
            ->setParameter('event', $event)
            ->where('meeting.state = :state')
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
            ->setParameter('participant', $participant)
            ->andWhere('meeting.state = :state')
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
    public function findByUserAndEventExceptSheet(Event $event, User $user, Sheet $sheet)
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
                (meeting.toSheet != :exceptSheet AND toParticipants.user = :user)
                OR (meeting.fromSheet != :exceptSheet AND fromParticipants.user = :user)'
            )
            ->setParameter('exceptSheet', $sheet)
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
    public function hasScheduledMeetingByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meeting.id')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->where('fromParticipant = :participant OR toParticipant = :participant')
            ->setParameter('participant', $participant)
            ->andWhere('meeting.state = :state')
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
    public function hasScheduledMeeting(Sheet $sheet)
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
}
