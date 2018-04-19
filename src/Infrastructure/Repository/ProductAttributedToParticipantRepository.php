<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ProductAttributedToParticipantRepository implements ProductAttributedToParticipantRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(ProductAttributedToParticipant $productAttributedToParticipant): void
    {
        $this->entityManager->persist($productAttributedToParticipant);
        $this->entityManager->flush($productAttributedToParticipant);
    }

    /**
     * {@inheritdoc}
     */
    public function findByProductAndParticipants(Product $product, array $participants): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('productAttributedToParticipant')
            ->from(ProductAttributedToParticipant::class, 'productAttributedToParticipant')
            ->where('productAttributedToParticipant.product = :product')
            ->andWhere('productAttributedToParticipant.participant IN (:participants)')
            ->setParameter('product', $product)
            ->setParameter('participants', $participants)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBatch(array $productAttributedToParticipants): void
    {
        foreach ($productAttributedToParticipants as $productAttributedToParticipant) {
            $this->entityManager->remove($productAttributedToParticipant);
        }

        $this->entityManager->flush($productAttributedToParticipants);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ProductAttributedToParticipant $productAttributedToParticipant): void
    {
        $this->entityManager->remove($productAttributedToParticipant);
        $this->entityManager->flush($productAttributedToParticipant);
    }
}
