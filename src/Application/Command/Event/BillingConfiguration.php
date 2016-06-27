<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class BillingConfiguration
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $iban;

    /**
     * @var string
     */
    public $billingAddress;

    /**
     * @var string
     */
    public $paymentCondition;

    /**
     * @var string
     */
    public $footers;

    /**
     * @var string
     */
    public $legalInfo;

    /**
     * BillingConfiguration constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
