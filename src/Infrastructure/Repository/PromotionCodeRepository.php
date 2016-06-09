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
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class PromotionCodeRepository implements PromotionCodeRepositoryInterface
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
    public function add(PromotionCode $promotionCode)
    {
        $this->entityManager->persist($promotionCode);
        $this->entityManager->flush($promotionCode);
    }

    /**
     * {@inheritdoc}
     */
    public function set(PromotionCode $promotionCode)
    {
        $this->entityManager->flush($promotionCode);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('promotion_code')
            ->from(PromotionCode::class, 'promotion_code')
            ->where('promotion_code.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
