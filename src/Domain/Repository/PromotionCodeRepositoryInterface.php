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
use Proximum\Vimeet\Domain\Model\PromotionCode;

interface PromotionCodeRepositoryInterface
{
    /**
     * @param PromotionCode $promotionCode
     */
    public function add(PromotionCode $promotionCode);

    /**
     * @param PromotionCode $promotionCode
     */
    public function set(PromotionCode $promotionCode);

    /**
     * @param Event $event
     *
     * @return PromotionCode[]
     */
    public function findByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return PromotionCode[]
     */
    public function findBoughtByEvent(Event $event): array;

    /**
     * @param PromotionCode $promotionCode
     *
     * @return PromotionCode[]
     */
    public function findDuplicate(PromotionCode $promotionCode);

    /**
     * @param Event  $event
     * @param string $code
     *
     * @return PromotionCode
     */
    public function findByEventAndCode(Event $event, $code);
}
