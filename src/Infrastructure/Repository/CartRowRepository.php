<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class CartRowRepository implements CartRowRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CartRow $cartRow
     */
    public function add(CartRow $cartRow)
    {
        $this->entityManager->persist($cartRow);
        $this->entityManager->flush($cartRow);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cartRow')
            ->from(CartRow::class, 'cartRow')
            ->join('cartRow.sheet', 'sheet', 'WITH', 'sheet.id = :id')
            ->setParameter('id', $sheet->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findCartRowPlanBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cartRow')
            ->from(CartRow::class, 'cartRow')
            ->join('cartRow.sheet', 'sheet', 'WITH', 'sheet.id = :id')
            ->setParameter('id', $sheet->getId())
            ->join('cartRow.product', 'product', 'WITH', 'product.type = :type')
            ->setParameter('type', Product::TYPE_PLAN)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CartRow $cartRow)
    {
        $this->entityManager->remove($cartRow);
        $this->entityManager->flush($cartRow);
    }
}
