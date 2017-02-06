<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Meeting;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;
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
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * RequestRepository constructor.
     *
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
        $queryBuilder->sendBy($sheet)->approved();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->receivedBy($sheet)->mostRecentFirst();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovedPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);
        $queryBuilder->receivedBy($sheet)->approved();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countApprovedRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->approved()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->pending()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countRefusedRequestSentBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->sendBy($sheet)->refused()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countApprovedPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->receivedBy($sheet)->approved()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countRefusedPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->receivedBy($sheet)->refused()->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = new RequestQueryBuilder($this->entityManager);

        return $queryBuilder->receivedBy($sheet)->pending()->count()->getIntResult();
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

        return $queryBuilder->sendBy($sheet)->count()->getIntResult();
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

        return $queryBuilder->receivedBy($sheet)->count()->getIntResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllRequestBySheet(Sheet $sheet, array $filters = [])
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
        ;

        if (!empty($filters) && isset($filters['disabled'])) {
            $queryBuilder->where('request.disabled = :disabled')
                ->setParameter('disabled', $filters['disabled']);
        }

        $this->filterQueryBuilder($queryBuilder, $sheet, $filters);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countAllByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(request)')
            ->from(Request::class, 'request', 'request.id')
            ->join('request.from', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('request.to', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->setParameter('event', $event);

        $this->requestsWithoutMeeting($queryBuilder);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndFilterByState(Event $event, $page, $limit, $locale, array $filter = [])
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request', 'request.id')
            ->join('request.from', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('request.to', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->where('request.disabled = FALSE')
            ->setParameter('event', $event)
            ->orderBy('request.createdAt', 'DESC');

        $this->requestsWithoutMeeting($queryBuilder);

        if (!empty($filter) && isset($filter['state'])) {
            $queryBuilder
                ->andWhere('request.state = :state')
                ->setParameter('state', $filter['state']);
        }

        list ($results, $count) = $this->paginator->getResultsAndTotal($queryBuilder, $page, $limit, 'request', 'id');

        return new PaginatedResult(array_map(function (Request $request) use ($locale) {
            return new RequestView(
                $request->getId(),
                $request->getFromSheet()->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($request->getFromSheet(), $locale),
                $request->getToSheet()->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($request->getToSheet(), $locale),
                $request->getState(),
                $request->getCreatedAt(),
                ''
            );
        }, $results), $page, $limit, $count);
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
            ->join('request.from', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('request.to', 'toSheet', 'WITH', 'toSheet.event = :event')
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
    public function getRequestsByEventAndUser(Event $event, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request');

        // By event and user
        $queryBuilder
            ->join('request.to', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->setParameter('event', $event)
            ->join('toSheet.participants', 'participant', 'WITH', 'participant.user = :user')
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
            ->setParameter('sheet', $sheet)
            ->setParameter('state', $state);

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
    public function countSheetState(Sheet $sheet, array $filters = [])
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
        ;

        if (!empty($filters) && isset($filters['disabled'])) {
            $queryBuilder->where('request.disabled = :disabled')
                ->setParameter('disabled', $filters['disabled']);
        }

        $this->filterQueryBuilder($queryBuilder, $sheet, $filters);

        return count($queryBuilder->getQuery()->getResult());
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestBetweenSheets(Sheet $one, Sheet $another)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->setMaxResults(1);

        // Between
        $queryBuilder
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->andX('request.from = :one', 'request.to = :another'),
                $queryBuilder->expr()->andX('request.from = :another', 'request.to = :one')
            ))
            ->setParameter('one', $one)
            ->setParameter('another', $another);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Sheet        $sheet
     * @param array        $filters
     */
    private function filterQueryBuilder(QueryBuilder &$queryBuilder, Sheet $sheet, array $filters)
    {
        if (!empty($filters['state']) && !Meeting\Constant::isSentOrReceiveFilter($filters['state'])) {
            $queryBuilder
                ->andWhere('request.to = :sheet OR request.from = :sheet');
        }

        // Filter by state
        if (!empty($filters['state']) && $filters['state'] != Meeting\Constant::FILTER_STATE_ALL) {
            if ($filters['state'] === Meeting\Constant::FILTER_STATE_RECEIVE) {
                $queryBuilder
                    ->andWhere('request.state = :state')
                    ->andWhere('request.to = :sheet')
                    ->setParameter('state', Meeting\Request::STATE_SENT);
            } elseif ($filters['state'] === Meeting\Constant::FILTER_STATE_SENT) {
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
        if (empty($filters['orderBy']) || $filters['orderBy'] === Sheet\Constant::ORDER_BY_CREATED_AT) {
            $queryBuilder->orderBy('request.createdAt', 'DESC');
        }

        // filter by participant type
        if (!empty($filters['type'])) {
            $queryBuilder
                ->leftJoin('request.from', 'fromSheet', 'WITH', 'fromSheet != :sheet')
                ->leftJoin('request.to', 'toSheet', 'WITH', 'toSheet != :sheet')
                ->andWhere('fromSheet.type IN (:types) OR toSheet.type IN (:types)')
                ->setParameter('types', $filters['type']);
        }

        if (!empty($filters['state'])) {
            // set sheet
            $queryBuilder->setParameter('sheet', $sheet);
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
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function findAccepted(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->andWhere('request.to = :sheet OR request.from = :sheet')
            ->andWhere('request.state = :state')
            ->setParameter('sheet', $sheet)
            ->setParameter('state', Request::STATE_APPROVED);

        return $queryBuilder->getQuery()->getResult();
    }
}
