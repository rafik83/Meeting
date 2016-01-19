<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
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
     * OrderRepository constructor.
     *
     * @param EntityManager      $entityManager
     * @param PaginatorInterface $paginator
     */
    public function __construct(EntityManager $entityManager, PaginatorInterface $paginator)
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
    public function paginate($page, $limit, Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order', '_order.id')
            ->where('_order.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        return $this->paginator->paginate($queryBuilder, $page, $limit);
    }
}
