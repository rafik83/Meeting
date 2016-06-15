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
    public function findCartRowPlanBySheet(Sheet $sheet)
    {
        return $this->findCartRowByProductTypeAndBySheet($sheet, Product::TYPE_PLAN);
    }

    /**
     * {@inheritdoc}
     */
    public function findCartRowPlanningBySheet(Sheet $sheet)
    {
        return $this->findCartRowByProductTypeAndBySheet($sheet, Product::TYPE_PLANNING);
    }

    /**
     * {@inheritdoc}
     */
    public function findCartRowParticipantBySheet(Sheet $sheet)
    {
        return $this->findCartRowByProductTypeAndBySheet($sheet, Product::TYPE_PARTICIPANT);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCartRowsBySheet(Sheet $sheet)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->getQuery()
            ->execute();
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
     * @param Sheet  $sheet
     * @param string $productType
     *
     * @return null|CartRow
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function findCartRowByProductTypeAndBySheet(Sheet $sheet, $productType)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cartRow')
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->join('cartRow.product', 'product', 'WITH', 'product.type = :type')
            ->setParameter('type', $productType)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
