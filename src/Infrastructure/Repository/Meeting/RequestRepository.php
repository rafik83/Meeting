<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Meeting;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardRequestView;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Meeting\Request\FilterQueryBuilder;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Meeting\Request\RequestQueryBuilder;

class RequestRepository implements RequestRepositoryInterface
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
     * RequestRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Request $request)
    {
        $this->entityManager->persist($request);
        $this->entityManager->flush($request);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Request $request)
    {
        $this->entityManager->flush($request);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Request $request)
    {
        $this->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(Request::class, 'request')
            ->where('request = :request')
            ->setParameter('request', $request)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest(Request $request)
    {
        $requestQueryBuilder = new RequestQueryBuilder($this->entityManager);

        $request = $requestQueryBuilder
            ->where('request = :request')
            ->setParameter('request', $request);

        return $request->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->sendBy($sheet)->mostRecentFirst();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovedRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->sendBy($sheet)->approved()->isFromAttending();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->receivedBy($sheet)->isEnabled()->mostRecentFirst();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovedPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->receivedBy($sheet)->approved()->isEnabled()->isToAttending();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countApprovedRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->approved()->isEnabled()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->pending()->isEnabled()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countRefusedRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->refused()->isEnabled()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countApprovedPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->receivedBy($sheet)->approved()->isEnabled()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countRefusedPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->receivedBy($sheet)->refused()->isEnabled()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingPropositionReceivedBySheet(Sheet $sheet, $attending = true)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        // Condition
        $queryBuilder->receivedBy($sheet)->pending()->isEnabled();

        if (true === $attending) {
            $queryBuilder->isFromAttending();
        }

        return $queryBuilder->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->receivedBy($sheet)->pending()->isEnabled()->isFromAttending();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingPropositionReceivedBySheetWithAvailableFromSheet(Sheet $toSheet, array $slotIds): int
    {
        return $this->countPendingPropositionReceivedBySheetWithAvailability($toSheet, $slotIds, true);
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingPropositionReceivedBySheetWithAvailableToSheet(Sheet $toSheet, array $slotIds): int
    {
        return $this->countPendingPropositionReceivedBySheetWithAvailability($toSheet, $slotIds, false);
    }

    /**
     * @param Sheet $toSheet
     * @param array $slotIds
     * @param bool  $checkFromSheetAvailability
     *
     * @return int
     */
    private function countPendingPropositionReceivedBySheetWithAvailability(
        Sheet $toSheet,
        array $slotIds,
        bool $checkFromSheetAvailability
    ): int {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('count(DISTINCT request.id)')
            ->from(Request::class, 'request')
            ->join(
                'request.from',
                'sheetFrom',
                'WITH',
                'request.to = :toSheet AND request.state = :pending AND request.disabled = false
                AND sheetFrom.attend = true'
            )
            ->join(
                Sheet\AvailableSlot::class,
                'availableSlot',
                'WITH',
                sprintf(
                    'availableSlot.sheet = request.%s AND availableSlot.slot IN (:slotIds)',
                    $checkFromSheetAvailability ? 'from' : 'to'
                )
            )
            ->setParameter('pending', Request::STATE_SENT)
            ->setParameter('toSheet', $toSheet)
            ->setParameter('slotIds', $slotIds)
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasPendingPropositionReceivedBySheet(Sheet $sheet)
    {
        return $this->countPendingPropositionReceivedBySheet($sheet) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function countRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->isEnabled()->isToAttending()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countApprovedRequestBySheets(Event $event, array $sheets): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('count(request.id) AS countRequest, sheet.id AS sheetId')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join(
                Request::class,
                'request',
                'WITH',
                'sheet.id IN (:sheets)
                AND request.state = :state
                AND request.event = :event
                AND (request.from = sheet OR request.to = sheet)
                AND request.disabled = FALSE
            ')
            ->groupBy('sheet.id')
            ->setParameter('state', Request::STATE_APPROVED)
            ->setParameter('event', $event)
            ->setParameter('sheets', $sheets)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequestSentBySheet(Sheet $sheet)
    {
        return $this->countRequestSentBySheet($sheet) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function countPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->receivedBy($sheet)->isEnabled()->isFromAttending()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllRequestBySheet(Sheet $sheet, array $filters = [], array $slotsToFilter = []): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->join('request.from', 'fromSheet', 'WITH', 'request.event = :event')
            ->join('request.to', 'toSheet')
            ->setParameter('event', $sheet->getEvent())
        ;

        if (!empty($filters) && isset($filters['disabled'])) {
            $queryBuilder->where('request.disabled = :disabled')
                ->setParameter('disabled', $filters['disabled']);
        }

        $this->filterQueryBuilder($queryBuilder, $sheet, $filters, $slotsToFilter);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getAllRequestBySheetAndSheets(
        Sheet $sheet,
        array $sheets,
        array $filters = [],
        array $slotsToFilter = []
    ): array {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->join('request.from', 'fromSheet', 'WITH', 'request.event = :event')
            ->join('request.to', 'toSheet')
            ->setParameter('event', $sheet->getEvent())
            ->setParameter('sheets', $sheets)
        ;

        if (!empty($filters) && isset($filters['disabled'])) {
            $queryBuilder->where('request.disabled = :disabled')
                ->setParameter('disabled', $filters['disabled']);
        }

        $queryBuilder
            ->andWhere('request.to IN (:sheets) OR request.from IN (:sheets)');

        $this->filterQueryBuilder($queryBuilder, $sheet, $filters, $slotsToFilter);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovedAndRefusedRequestBySheet(Sheet $sheet): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->where('
                (request.from = :sheet OR request.to = :sheet) AND
                (request.state = :stateApproved OR request.state = :stateRefused)
            ')
            ->setParameter('sheet', $sheet)
            ->setParameter('stateApproved', Request::STATE_APPROVED)
            ->setParameter('stateRefused', Request::STATE_REFUSED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countAllByEvent(Event $event): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(request.id)')
            ->from(Request::class, 'request')
            ->where('request.event = :event AND request.disabled = false')
            ->setParameter('event', $event);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countApprovedByEvent(Event $event): int
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->count()->fromEvent($event)->approved()->isEnabled();

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countBySheetWithPriority(Sheet $sheet): int
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->count()->isEnabled()
            ->where('request.from = :sheet AND request.fromPriority = true OR request.to = :sheet AND request.toPriority = true')
            ->setParameter('sheet',  $sheet);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingByEvent(Event $event): int
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->count()->fromEvent($event)->pending()->isEnabled();

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countRefusedByEvent(Event $event): int
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->count()->fromEvent($event)->refused()->isEnabled();

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndFilterByState(Event $event, $page, $limit, $locale, array $filter = [])
    {
        $queryBuilder = new FilterQueryBuilder($this->entityManager, $event);

        if (!empty($filter)) {
            if (!empty($filter['state'])) {
                $filterState = $filter['state'];

                if (Request::STATE_PLANNED === $filterState) {
                    $queryBuilder->filterPlanned();
                } else {
                    $queryBuilder->filterByState($filterState);
                }
            }

            if (!empty($filter['orderBy']) && in_array($filter['orderBy'], $this->getOrderBy())) {
                $queryBuilder->order($filter['orderBy']);
            }
        }

        list($results, $count) = $this->paginator->getResultsAndTotal($queryBuilder, $page, $limit, 'request', 'id');

        return new PaginatedResult(array_map(function (Request $request) {
            return new RequestView(
                $request->getId(),
                $request->getFromSheet()->getId(),
                $request->getFromSheet()->getTitle(),
                $request->getToSheet()->getId(),
                $request->getToSheet()->getTitle(),
                $request->getState(),
                $request->getCreatedAt(),
                $request->getStateUpdatedAt(),
                '',
                $request->hasMeeting()
            );
        }, $results), $page, $limit, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventWithHydratationOfElement(Event $event, int $page, int $limit): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request, fromSheet, toSheet, meeting, fromType, toType')
            ->from(Request::class, 'request', 'request.id')
            ->join('request.from', 'fromSheet', 'WITH', 'request.event = :event AND request.disabled = false')
            ->join('request.to', 'toSheet')
            ->join('fromSheet.type', 'fromType')
            ->join('toSheet.type', 'toType')
            ->leftJoin('request.meeting', 'meeting')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hydrateParticipants(array $requests): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request, fromParticipant, toParticipant')
            ->from(Request::class, 'request', 'request.id')
            ->leftJoin('request.fromParticipants', 'fromParticipant')
            ->leftJoin('request.toParticipants', 'toParticipant')
            ->where('request.id IN (:requests)')
            ->setParameter('requests', $requests)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAcceptedByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request', 'request.id')
            ->join(
                'request.from',
                'fromSheet',
                'WITH',
                'fromSheet.event = :event AND fromSheet.inCatalog = true
                AND fromSheet.enable = true AND fromSheet.attend = true'
            )
            ->join(
                'request.to',
                'toSheet',
                'WITH',
                'toSheet.event = :event AND toSheet.inCatalog = true
                AND toSheet.enable = true AND toSheet.attend = true'
            )
            ->join('fromSheet.participants', 'fromParticipants')
            ->join('toSheet.participants', 'toParticipants')
            ->where('request.state = :approved')
            ->andWhere('request.disabled = false')
            ->setParameter('event', $event)
            ->setParameter('approved', Request::STATE_APPROVED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestsPlacedByEventAndUser(Event $event, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->join('request.meeting', 'meeting')
            ->leftJoin('meeting.fromParticipants', 'fp')
            ->leftJoin('meeting.toParticipants', 'tp')
            ->where('request.event = :event')
            ->andWhere('(fp.user = :user OR tp.user = :user)')
            ->setParameter('event', $event)
            ->setParameter('user', $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnassignedRequestsBySheetAndEvent(Sheet $sheet, $state)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->andWhere('request.to = :sheet OR request.from = :sheet')
            ->andWhere('request.state = :state')
            ->andWhere('request.disabled = false')
            ->setParameter('sheet', $sheet)
            ->setParameter('state', $state);

        $this->requestsWithoutMeeting($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnallocatedRequestForSheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->andWhere('request.to IN (:sheets) OR request.from IN (:sheets)')
            ->andWhere('request.state = :state')
            ->andWhere('request.disabled = false')
            ->setParameter('sheets', $sheets)
            ->setParameter('state', Request::STATE_APPROVED);

        $this->requestsWithoutMeeting($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestBetweenSheetsWithStates(Sheet $one, Sheet $another, array $states)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request');

        // Between
        $queryBuilder
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->andX('request.from = :one', 'request.to = :another'),
                $queryBuilder->expr()->andX('request.from = :another', 'request.to = :one')
            ))
            ->setParameter('one', $one)
            ->setParameter('another', $another);

        // State
        $queryBuilder
            ->andWhere('request.state IN (:states)')
            ->setParameter('states', $states);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countSheetState(Sheet $sheet, array $filters = [], array $slotsToFilter = [])
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->join('request.from', 'fromSheet', 'WITH', 'request.event = :event')
            ->join('request.to', 'toSheet')
            ->setParameter('event', $sheet->getEvent())
        ;

        if (!empty($filters)) {
            if (isset($filters['disabled'])) {
                $queryBuilder->andWhere('request.disabled = :disabled')
                    ->setParameter('disabled', $filters['disabled']);
            }

            if (isset($filters['isToAttending'])) {
                $queryBuilder->andWhere('toSheet.attend = true');
            }

            if (isset($filters['isFromAttending'])) {
                $queryBuilder->andWhere('fromSheet.attend = true');
            }
        }

        $this->filterQueryBuilder($queryBuilder, $sheet, $filters, $slotsToFilter);

        return count($queryBuilder->getQuery()->getResult());
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestBetweenSheets(Sheet $one, Sheet $another)
    {
        $queryBuilder = $this->getRequestBetweenSheetsQueryBuilder($one, $another);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequestBetweenSheets(Sheet $one, Sheet $another): bool
    {
        $queryBuilder = $this->getRequestBetweenSheetsQueryBuilder($one, $another);
        $queryBuilder->andWhere('request.disabled = false');

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestsOfSheetsWithSheets(
        Event $event,
        array $sheets,
        array $sheetsMet,
        $state = null,
        $type = null,
        $user = null
    ) {
        $queryBuilder = $this->requestOfSheetsWithSheets($event, $sheets, $sheetsMet, $state, $type, $user);

        $queryBuilder->select('request, fromSheet, toSheet, meeting');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return Request[]
     */
    public function getApprovedByType(Event $event, Type $type): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('request, fromSheet, toSheet')
            ->from(Request::class, 'request')
            ->innerJoin(
                'request.from',
                'fromSheet',
                'WITH',
                'request.event = :event
                    AND request.disabled = false
                    AND request.state = :state
                '
            )
            ->innerJoin('request.to', 'toSheet')
            ->innerJoin('fromSheet.type', 'fromType')
            ->innerJoin('toSheet.type', 'toType')
            ->where('fromType = :type OR toType = :type')
            ->setParameter('event', $event)
            ->setParameter('type', $type)
            ->setParameter('state', Request::STATE_APPROVED)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function countRequestOfSheetsWithSheets(
        Event $event,
        array $sheets,
        array $sheetsMet,
        $state = null,
        $type = null,
        $user = null
    ) {
        $queryBuilder = $this->requestOfSheetsWithSheets($event, $sheets, $sheetsMet, $state, $type, $user);

        $queryBuilder->select('count(request)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Event            $event
     * @param Sheet[]          $sheets
     * @param Sheet[]          $sheetsMet
     * @param string|null      $state
     * @param string|null      $type
     * @param User|string|null $user
     *
     * @return QueryBuilder
     */
    private function requestOfSheetsWithSheets(
        Event $event,
        array $sheets,
        array $sheetsMet,
        $state = null,
        $type = null,
        $user = null
    ) {
        $typeCondition = '(
            (fromSheet.id IN (:sheets) AND toSheet.id IN (:sheetsMet))
            OR (toSheet.id IN (:sheets) AND fromSheet.id IN (:sheetsMet))
        )';
        $stateCondition = '1 = 1';

        if (null !== $state && in_array($state, Request::getAllStates())) {
            $stateCondition = sprintf("request.state = '%s'", $state);
        }

        if (null !== $type) {
            if (Request::TYPE_REQUEST === $type) {
                $typeCondition = '(fromSheet.id IN (:sheets) AND toSheet.id IN (:sheetsMet))';
            } elseif (Request::TYPE_PROPOSITION === $type) {
                $typeCondition = '(toSheet.id IN (:sheets) AND fromSheet.id IN (:sheetsMet))';
            }
        }

        // filter meeting request with fromParticipants or toParticipants empty
        if (FilterRequestView::NO_PREFERENCE === $user) {
            $typeCondition = '(
                (fromSheet.id IN (:sheets) AND toSheet.id IN (:sheetsMet)) AND fp.id IS NULL
                OR
                (toSheet.id IN (:sheets) AND fromSheet.id IN (:sheetsMet)) AND tp.id IS NULL
            )';
        }

        $queryBuilder = new FilterQueryBuilder($this->entityManager, $event);

        $queryBuilder
            ->leftJoin('request.meeting', 'meeting')
            ->where(sprintf('%s AND %s', $typeCondition, $stateCondition))
            ->setParameter('sheets', $sheets)
            ->setParameter('sheetsMet', $sheetsMet);

        if ($user instanceof User) {
            $queryBuilder
                ->leftJoin('request.fromParticipants', 'fp')
                ->leftJoin('request.toParticipants', 'tp')
                ->andWhere('(tp.user = :user OR fp.user = :user)')
                ->setParameter('user', $user)
            ;
        }

        if (FilterRequestView::NO_PREFERENCE === $user) {
            $queryBuilder
                ->leftJoin('request.fromParticipants', 'fp')
                ->leftJoin('request.toParticipants', 'tp');
        }

        if (Request::STATE_PLANNED === $state) {
            $queryBuilder->filterPlanned();
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Sheet        $sheet
     * @param array        $filters
     * @param array        $slotsToFilter
     */
    private function filterQueryBuilder(
        QueryBuilder &$queryBuilder,
        Sheet $sheet,
        array $filters,
        array $slotsToFilter = []
    ) {
        if (!empty($filters['state']) && !Meeting\Constant::isSentOrReceiveFilter($filters['state'])) {
            $queryBuilder
                ->andWhere('request.to = :sheet OR request.from = :sheet');
        }

        // Filter by state
        if (!empty($filters['state']) && Meeting\Constant::FILTER_STATE_ALL != $filters['state']) {
            if (Meeting\Constant::FILTER_STATE_RECEIVE === $filters['state']) {
                $queryBuilder
                    ->andWhere('request.state = :state')
                    ->andWhere('request.to = :sheet')
                    ->setParameter('state', Meeting\Request::STATE_SENT);
            } elseif (Meeting\Constant::FILTER_STATE_SENT === $filters['state']) {
                $queryBuilder
                    ->andWhere('request.from = :sheet')
                    ->andWhere('request.state = :state')
                    ->setParameter('state', Meeting\Request::STATE_SENT);
            } else {
                $queryBuilder
                    ->andWhere('request.state = :state')
                    ->setParameter('state', Meeting\Constant::getMappedRequestState($filters['state']));
            }
        }

        // order by
        if (empty($filters['orderBy']) || Sheet\Constant::ORDER_BY_CREATED_AT === $filters['orderBy']) {
            $queryBuilder->orderBy('request.createdAt', 'DESC');
        }

        // filter by participant type
        if (!empty($filters['type'])) {
            $queryBuilder
                ->andWhere('(fromSheet != :sheet AND fromSheet.type IN (:types)) OR (toSheet != :sheet AND toSheet.type IN (:types))')
                ->setParameter('types', $filters['type']);
        }

        // filter by participant category
        if (!empty($filters['category'])) {
            $queryBuilder
                ->join('fromSheet.type', 'fromType')
                ->join('toSheet.type', 'toType')
                ->leftJoin('fromType.categories', 'fromCategory')
                ->leftJoin('toType.categories', 'toCategory')
                ->andWhere('(fromSheet != :sheet AND fromCategory IN (:categories)) OR (toSheet != :sheet AND toCategory IN (:categories))')
                ->setParameter('categories', $filters['category'])
                ->setParameter('sheet', $sheet);
        }

        if (!empty($filters['state'])) {
            // set sheet
            $queryBuilder->setParameter('sheet', $sheet);
        }

        if (!empty($filters['availableSlot'])
            && Meeting\Constant::FILTER_AVAILABLE_SLOT_IDS_EVERYONE !== $filters['availableSlot']
            && (Meeting\Constant::FILTER_AVAILABLE_SLOT_IDS_AVAILABLE === $filters['availableSlot']
                || (Meeting\Constant::FILTER_AVAILABLE_SLOT_IDS_SLOT === $filters['availableSlot']
                    && !empty($filters['slot_id'])
                )
            )
        ) {
            $queryBuilder
                ->join('fromSheet.availableSlots', 'fromAvailableSlot', 'WITH', 'fromAvailableSlot.slot IN (:slots)')
                ->join('toSheet.availableSlots', 'toAvailableSlot', 'WITH', 'toAvailableSlot.slot IN (:slots)')
                ->setParameter('slots', array_map(function ($slot) {
                    if ($slot instanceof MeetingSlot) {
                        return $slot->getId();
                    } elseif ($slot instanceof AvailableSlotView) {
                        return $slot->id;
                    }

                    return null;
                }, $slotsToFilter))
            ;
        }
    }

    /**
     * Filter Requests that are not attached to Meeting
     *
     * @param QueryBuilder $queryBuilder
     */
    private function requestsWithoutMeeting(QueryBuilder &$queryBuilder)
    {
        $queryBuilder->andWhere('NOT EXISTS(SELECT m.id FROM Entity:Meeting m where m.request = request)');
    }

    /**
     * {@inheritdoc}
     */
    public function update(Request $request)
    {
        $this->entityManager->flush($request);
    }

    /**
     * {@inheritdoc}
     */
    public function findApproved(Sheet $sheet): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->andWhere('request.to = :sheet OR request.from = :sheet')
            ->andWhere('request.state = :state')
            ->andWhere('request.disabled = false')
            ->setParameter('sheet', $sheet)
            ->setParameter('state', Request::STATE_APPROVED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function participantIsAssignedToAccepted(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('count(request)')
            ->from(Request::class, 'request')
            ->leftjoin('request.fromParticipants', 'fromParticipant')
            ->leftjoin('request.toParticipants', 'toParticipant')
            ->where(
                'request.state = :approved AND request.disabled = false
                AND (fromParticipant.id = :participant OR toParticipant.id = :participant)'
            )
            ->andWhere('NOT EXISTS(SELECT m.id FROM Entity:Meeting m where m.request = request)')
            ->setParameter('participant', $participant)
            ->setParameter('approved', Request::STATE_APPROVED);

        return ((int) $queryBuilder->getQuery()->getSingleScalarResult()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssignedRequestByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request.id')
            ->from(Request::class, 'request')
            ->leftjoin('request.fromParticipants', 'fromParticipant')
            ->leftjoin('request.toParticipants', 'toParticipant')
            ->where(
                '(fromParticipant.id = :participant OR toParticipant.id = :participant)
                AND request.disabled = false'
            )
            ->setParameter('participant', $participant)
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findBySheets(Event $event, array $sheets, array $states, bool $withoutMeeting): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->andWhere('request.event = :event')
            ->andWhere('request.to IN (:sheets) OR request.from IN (:sheets)')
            ->andWhere('request.state IN (:states)')
            ->andWhere('request.disabled = false')
            ->setParameter('event', $event)
            ->setParameter('sheets', $sheets)
            ->setParameter('states', $states)
        ;

        if ($withoutMeeting) {
            $this->requestsWithoutMeeting($queryBuilder);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return array
     */
    private function getOrderBy()
    {
        return [
            RequestRepositoryInterface::ORDER_BY_CREATE_AT_ASC,
            RequestRepositoryInterface::ORDER_BY_CREATE_AT_DESC,
            RequestRepositoryInterface::ORDER_BY_STATE_UPDATED_AT_ASC,
            RequestRepositoryInterface::ORDER_BY_STATE_UPDATED_AT_DESC,
        ];
    }

    /**
     * @param Sheet $one
     * @param Sheet $another
     *
     * @return QueryBuilder
     */
    private function getRequestBetweenSheetsQueryBuilder(Sheet $one, Sheet $another): QueryBuilder
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->setMaxResults(1);

        // Between
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX('request.from = :one', 'request.to = :another'),
                    $queryBuilder->expr()->andX('request.from = :another', 'request.to = :one')
                )
            )
            ->setParameter('one', $one)
            ->setParameter('another', $another);

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function hasApprovedMeetingRequest(Sheet $sheet, Sheet $sheetMet): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->join('request.to', 'toSheet')
            ->join('request.from', 'fromSheet')
            ->where('fromSheet = :sheet OR toSheet = :sheet')
            ->andWhere('fromSheet = :sheetMet OR toSheet = :sheetMet')
            ->andWhere('request.state = :state')
            ->setParameter('sheet', $sheet)
            ->setParameter('sheetMet', $sheetMet)
            ->setParameter('state', Request::STATE_APPROVED)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovedAndPrioritizedWithoutMeeting(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->andWhere('request.event = :event')
            ->andWhere('request.state = :approved_state')
            ->andWhere('request.disabled = false')
            ->andWhere('(request.fromPriority = true or request.toPriority = true)')
            ->setParameter('event', $event)
            ->setParameter('approved_state', Meeting\Request::STATE_APPROVED)
        ;

        $this->requestsWithoutMeeting($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getDashboardRequestViewsByEvent(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select(sprintf('NEW %s(fromType.id, request.state, meeting.id)', DashboardRequestView::class))
            ->from(Request::class, 'request')
            ->join('request.from', 'fromSheet', 'WITH', 'request.event = :event AND request.disabled = false')
            ->join('fromSheet.type', 'fromType')
            ->leftJoin('request.meeting', 'meeting')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();
    }
}
