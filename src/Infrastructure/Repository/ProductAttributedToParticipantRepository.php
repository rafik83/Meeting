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
use Proximum\Vimeet\Domain\Model\Participant;
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
    public function participantHasAtLeastOneProduct(Participant $participant, array $products): bool
    {
        return null !== $this->entityManager->createQueryBuilder()
            ->select('productAttributedToParticipant')
            ->from(ProductAttributedToParticipant::class, 'productAttributedToParticipant')
            ->where('productAttributedToParticipant.product IN (:products)')
            ->andWhere('productAttributedToParticipant.participant = :participant')
            ->setParameter('products', $products)
            ->setParameter('participant', $participant)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
