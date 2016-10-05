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
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
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
    public function __construct(EntityManager $entityManager, Paginator $paginator, SheetInfoGuesser $sheetInfoGuesser)
    {
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
    public function getAllRequestBySheet(Sheet $sheet, array $filters = [])
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->where('request.to = :sheet')
            ->orWhere('request.from = :sheet')
            ->setParameter('sheet', $sheet);

        if (!empty($filters['state']) && $filters['state'] != Request::STATE_ALL) {
            $queryBuilder
                ->andWhere('request.state = :state')
                ->setParameter('state', $filters['state']);
        }

        if (empty($filters['orderBy']) || $filters['orderBy'] === Sheet\Constant::ORDER_BY_CREATED_AT) {
            $queryBuilder->orderBy('request.createdAt', 'DESC');
        }

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
            ->setParameter('event', $event)
            ->where('request.meeting IS NULL');

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
            ->setParameter('event', $event)
            ->where('request.meeting IS NULL')
            ->orderBy('request.createdAt', 'DESC');

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
                $this->sheetInfoGuesser->guessSheetName($request->getFromSheet(), $locale),
                $request->getToSheet()->getId(),
                $this->sheetInfoGuesser->guessSheetName($request->getToSheet(), $locale),
                $request->getState(),
                $request->getCreatedAt(),
                ''
            );
        }, $results), $page, $limit, $count);
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
     * @param Sheet  $sheet
     * @param string $state
     *
     * @return int
     */
    public function countSheetState(Sheet $sheet, $state)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->where('request.from = :sheet')
            ->orWhere('request.to = :sheet')
            ->setParameter('sheet', $sheet);

        if ($state !== Request::STATE_ALL) {
            $queryBuilder
                ->andWhere('request.state = :state')
                ->setParameter('state', $state);
        }

        return count($queryBuilder->getQuery()->getResult());
    }
}
