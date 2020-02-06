<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;

interface PromotionCodeGroupRepositoryInterface
{
    public function add(PromotionCodeGroup $promotionCodeGroup): void;

    public function set(PromotionCodeGroup $promotionCodeGroup): void;

    /**
     * @param Event $event
     *
     * @return PromotionCodeGroup[]
     */
    public function findByEvent(Event $event): array;
}
