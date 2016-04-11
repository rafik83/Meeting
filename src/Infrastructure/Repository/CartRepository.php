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
use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
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
     * @param Cart $cart
     */
    public function add(Cart $cart)
    {
        $this->entityManager->persist($cart);
        $this->entityManager->flush($cart);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Cart $cart)
    {
        $this->entityManager->flush($cart);
    }

    /**
     * @param Sheet $sheet
     *
     * @return Cart
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cart')
            ->from('Entity:Cart', 'cart')
            ->join('cart.sheet', 'sheet', 'WITH', 'sheet.id = :id')
            ->setParameter('id', $sheet->getId())
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Cart $cart)
    {
        $this->entityManager->remove($cart);
        $this->entityManager->flush($cart);
    }
}
