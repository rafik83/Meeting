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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Numero\OrderNumeroView;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
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
    public function add(Order $order)
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush($order);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Order $order)
    {
        $this->entityManager->flush($order);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order', '_order.id')
            ->where('_order.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('_order.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event, array $filters, $page, $limit, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order', '_order.id')
            ->join('_order.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->setParameter('event', $event)
            ->orderBy('_order.createdAt', 'DESC');

        if (isset($filters['product']) && $filters['product'] instanceof Product) {
            $queryBuilder
                ->join('_order.rows', 'rows', 'WITH', 'rows.product = :product')
                ->setParameter('product', $filters['product']);
        }

        if (isset($filters['enabled'])) {
            $queryBuilder
                ->andWhere('sheet.enable = :enable')
                ->setParameter('enable', $filters['enabled']);
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, '_order', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function findByNumero(OrderNumeroView $orderNumeroView)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order')
            ->join('_order.sheet', 'sheet')
            ->where('_order.id = :orderId')
            ->andWhere('_order.sheet = :sheetId')
            ->andWhere('sheet.event = :eventId')
            ->setParameter('eventId', $orderNumeroView->eventId)
            ->setParameter('sheetId', $orderNumeroView->sheetId)
            ->setParameter('orderId', $orderNumeroView->orderId)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
