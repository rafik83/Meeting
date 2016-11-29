<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Event;

class DashboardViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var float|int
     */
    public $totalOrders;

    /**
     * @var float|int
     */
    public $totalRemainingToPay;

    /**
     * @var float|int
     */
    public $totalPaid;

    /**
     * DashboardViewQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
