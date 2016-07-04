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
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class PromotionCodeRowRepository implements PromotionCodeRowRepositoryInterface
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
     * @param PromotionCodeRow $promotionCodeRow
     */
    public function add(PromotionCodeRow $promotionCodeRow)
    {
        $this->entityManager->persist($promotionCodeRow);
        $this->entityManager->flush($promotionCodeRow);
    }

    /**
     * @param PromotionCodeRow $promotionCodeRow
     */
    public function set(PromotionCodeRow $promotionCodeRow)
    {
        $this->entityManager->flush($promotionCodeRow);
    }

    /**
     * @param Sheet $sheet
     *
     * @return PromotionCodeRow[]
     */
    public function findBySheet($sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('promotionCodeRow')
            ->from(PromotionCodeRow::class, 'promotionCodeRow')
            ->where('promotionCodeRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        return $queryBuilder->getQuery()->getResult();
    }
}
