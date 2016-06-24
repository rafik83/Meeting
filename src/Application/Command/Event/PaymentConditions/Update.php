<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\PaymentConditions;

use Proximum\Vimeet\Domain\Model;

class Update
{
    /**
     * @var Model\Event
     */
    public $event;

    /**
     * @var bool
     */
    public $allowDeposit;

    /**
     * @var \DateTimeInterface
     */
    public $depositUntil;

    /**
     * @var float
     */
    public $minimumForDeposit;

    /**
     * @var int
     */
    public $deposit;

    /**
     * Update constructor.
     * @param Model\Event $event
     */
    public function __construct(Model\Event $event)
    {
        $this->event             = $event;
        $this->allowDeposit      = $event->getConfiguration()->isAllowDeposit();
        $this->depositUntil      = $event->getConfiguration()->getDepositUntil();
        $this->deposit           = $event->getConfiguration()->getDeposit();
        $this->minimumForDeposit = $event->getConfiguration()->getMinimumForDeposit();
    }
}