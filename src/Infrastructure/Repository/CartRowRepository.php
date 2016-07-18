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
     * @param CartRow $cartRow
     */
    public function set(CartRow $cartRow)
    {
        $this->entityManager->flush($cartRow);
    }

    /**
     * @param Sheet $sheet
     * @param array $cartRows
     */
    public function deleteWhereNotIn(Sheet $sheet, array $cartRows)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->andWhere('cartRow NOT IN (:cartRows)')
            ->setParameter('cartRows', $cartRows);

        $queryBuilder->getQuery()->execute();
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
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CartRow $cartRow)
    {
        $this->entityManager->remove($cartRow);
        $this->entityManager->flush($cartRow);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteForSheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        $queryBuilder->getQuery()->execute();
    }
}
