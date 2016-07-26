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
use Proximum\Vimeet\Domain\Model\CartStep;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;

class CartStepRepository implements CartStepRepositoryInterface
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
     * {@inheritdoc}
     */
    public function add(CartStep $cartStep)
    {
        $this->entityManager->persist($cartStep);
        $this->entityManager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function set(CartStep $cartStep)
    {
        $this->entityManager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cartStep')
            ->from(CartStep::class, 'cartStep')
            ->join('cartStep.sheet', 'sheet', 'WITH', 'sheet.id = :id')
            ->setParameter('id', $sheet->getId())
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CartStep $cartStep)
    {
        $this->entityManager->remove($cartStep);
        $this->entityManager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteForSheet(Sheet $sheet)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(CartStep::class, 'cartStep')
            ->where('cartStep.sheet = :sheetId')
            ->setParameter('sheetId', $sheet->getId())
            ->getQuery()
            ->execute()
        ;

        $this->entityManager->flush();
    }
}
