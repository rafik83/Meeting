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
use Symfony\Bridge\Doctrine\ManagerRegistry;

class CartStepRepository implements CartStepRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->manager = $registry->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function add(CartStep $cartStep)
    {
        $this->manager->persist($cartStep);
        $this->manager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function set(CartStep $cartStep)
    {
        $this->manager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->manager
            ->createQueryBuilder()
            ->select('cartStep')
            ->from(CartStep::class, 'cartStep')
            ->where('cartStep.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CartStep $cartStep)
    {
        $this->manager->remove($cartStep);
        $this->manager->flush($cartStep);
    }

    public function deleteForSheet(Sheet $sheet)
    {
        $this
            ->manager
            ->createQueryBuilder()
            ->delete(CartStep::class, 'cartStep')
            ->where('cartStep.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->getQuery()
            ->execute()
        ;

        $this->manager->flush();
    }
}
