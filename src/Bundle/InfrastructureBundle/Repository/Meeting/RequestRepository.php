<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository\Meeting;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;

class RequestRepository implements RequestRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * RequestRepository constructor.
     *
     * @param EntityManager      $entityManager
     * @param PaginatorInterface $paginator
     * @param SheetInfoGuesser   $sheetInfoGuesser
     */
    public function __construct(EntityManager $entityManager, PaginatorInterface $paginator, SheetInfoGuesser $sheetInfoGuesser)
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
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from('Entity:Meeting\Request', 'request')
            ->where('request.from = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('request.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPropositionReceivedBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from('Entity:Meeting\Request', 'request')
            ->where('request.to = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('request.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllRequestBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from('Entity:Meeting\Request', 'request')
            ->where('request.to = :sheet')
            ->orWhere('request.from = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('request.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingByEvent(Event $event, $page, $limit)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('request')
            ->from(Request::class, 'request')
            ->join('request.from', 'fromSheet', 'WITH', 'fromSheet.event = :event')
            ->join('request.to', 'toSheet', 'WITH', 'toSheet.event = :event')
            ->setParameter('event', $event)
            ->where('request.state = :state')
            ->setParameter('state', Request::STATE_APPROVED)
            ->andWhere('request.meeting IS NULL');

        $pagination = $this->paginator->paginate($queryBuilder, $page, $limit);

        $pagination->setItems(array_map(function (Request $request) {
            return new RequestView(
                $request->getId(),
                $this->sheetInfoGuesser->guessSheetInfo($request->getFromSheet()),
                $this->sheetInfoGuesser->guessSheetInfo($request->getToSheet()),
                $request->getState(),
                $request->getCreatedAt(),
                ''
            );
        }, $pagination->getItems()));

        return $pagination;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestsByEventAndUser($event, User $user)
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
}
