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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order\PromotionCode as OrderPromotionCode;
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
    public function findByEvent(Event $event): array
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

    /**
     * {@inheritdoc}
     */
    public function findBoughtByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('promotion_code')
            ->from(PromotionCode::class, 'promotion_code')
            ->innerJoin(
                OrderPromotionCode::class,
                'order_promotion_code',
                'WITH',
                '
                    order_promotion_code.promotionCode = promotion_code
                    AND promotion_code.event = :event
                '
            )
            ->innerJoin('order_promotion_code.order', '_order', 'WITH', '_order.cancelled = false')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findDuplicate(PromotionCode $promotionCode)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('promotion_code')
            ->from(PromotionCode::class, 'promotion_code')
            ->where('promotion_code.event = :event')
            ->setParameter('event', $promotionCode->getEvent())
            ->andWhere('promotion_code.code = :code')
            ->setParameter('code', $promotionCode->getCode());

        if ($this->entityManager->contains($promotionCode)) {
            $queryBuilder
                ->andWhere('promotion_code != :promotion_code')
                ->setParameter('promotion_code', $promotionCode);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndCode(Event $event, $code)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('promotion_code')
            ->from(PromotionCode::class, 'promotion_code')
            ->where('promotion_code.event = :event')
            ->setParameter('event', $event)
            ->andWhere('promotion_code.code = :code')
            ->setParameter('code', $code)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
