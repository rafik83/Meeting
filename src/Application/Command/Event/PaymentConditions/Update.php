<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\PaymentConditions;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Update implements Command
{
    /** @var Event */
    public $event;

    /** @var bool */
    public $allowDeposit;

    /** @var \DateTimeInterface|null */
    public $depositUntil;

    /** @var float */
    public $minimumForDeposit;

    /** @var int */
    public $deposit;

    /** @var array*/
    public $paymentModes;

    public function __construct(Event $event)
    {
        $this->event             = $event;
        $this->allowDeposit      = $event->getConfiguration()->isAllowDeposit();
        $this->depositUntil      = $event->getConfiguration()->getDepositUntil();
        $this->deposit           = $event->getConfiguration()->getDeposit();
        $this->minimumForDeposit = $event->getConfiguration()->getMinimumForDeposit();
        $this->paymentModes      = $event->getConfiguration()->getPaymentModes();
    }

    public function isPaymentModesNotEmpty(): bool
    {
        return !empty($this->paymentModes);
    }
}
