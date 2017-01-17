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
                $this->sheetInfoGuesser->guessSheetName($meeting->getFromSheet(), $locale),
                $this->sheetInfoGuesser->guessSheetName($meeting->getToSheet(), $locale),
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
            ->select('meeting')
            ->from(Meeting::class, 'meeting', 'meeting.id')
            ->join('meeting.fromSheet', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('meeting.toSheet', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->setParameter('event', $event)
            ->where('meeting.state = :state')
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
            ->select('meeting, fromSheet, toSheet')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
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
    public function countByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(meeting)')
            ->from(Meeting::class, 'meeting')
            ->join('meeting.fromSheet', 'fromSheet')
            ->join('meeting.toSheet', 'toSheet')
            ->leftJoin('meeting.fromParticipants', 'fromParticipant')
            ->leftJoin('meeting.toParticipants', 'toParticipant')
            ->where('fromParticipant = :participant OR toParticipant = :participant')
            ->setParameter('participant', $participant)
            ->andWhere('meeting.state = :state')
            ->setParameter('state', Meeting::STATE_SCHEDULED);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll(Event $event)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Meeting::class, 'meeting')
            ->join('meeting.slot', 'slot', 'WITH', 'slot.event = :event')
            ->setParameter('event', $event)
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
    public function checkMeetingFromSlot(MeetingSlot $meetingSlot)
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

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
