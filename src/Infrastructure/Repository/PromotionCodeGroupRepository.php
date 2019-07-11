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
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;

class PromotionCodeGroupRepository implements PromotionCodeGroupRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(PromotionCodeGroup $promotionCodeGroup)
    {
        $this->entityManager->persist($promotionCodeGroup);
        $this->entityManager->flush($promotionCodeGroup);
    }
}
