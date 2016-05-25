<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PurchasingFunnel;

interface PurchasingFunnelRepositoryInterface
{
    /**
     * @param PurchasingFunnel $purchasingFunnel
     */
    public function add(PurchasingFunnel $purchasingFunnel);

    /**
     * @param PurchasingFunnel $purchasingFunnel
     */
    public function set(PurchasingFunnel $purchasingFunnel);

    /**
     * @param Event $event
     *
     * @return PurchasingFunnel[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event[] $events
     *
     * @return PurchasingFunnel[]
     */
    public function findByEvents(array $events);
}
