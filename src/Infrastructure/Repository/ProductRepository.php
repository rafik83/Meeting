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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * OrderRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Product $product)
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush($product);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndTypes(Event $event, array $types)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->setParameter('event', $event)
            ->andWhere('product.type IN (:types)')
            ->setParameter('types', $types);

        return $queryBuilder->getQuery()->getResult();
    }
}
