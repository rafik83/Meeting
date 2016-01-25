<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

use Proximum\Vimeet\Application\Components\Sheet\Order\OrderView;

class ProformaView
{
    /**
     * @var string
     */
    public $eventName;

    /**
     * @var array
     */
    public $participants;

    /**
     * @var OrderView
     */
    public $orderView;

    /**
     * @var OrganizerView
     */
    public $organizerView;

    /**
     * @var BillingView
     */
    public $billingView;

    /**
     * ProformaView constructor.
     *
     * @param string        $eventName
     * @param array         $participants
     * @param OrderView     $orderView
     * @param OrganizerView $organizerView
     * @param BillingView   $billingView
     */
    public function __construct(
        $eventName,
        array $participants,
        OrderView $orderView,
        OrganizerView $organizerView,
        BillingView $billingView
    ) {
        $this->eventName     = $eventName;
        $this->participants  = $participants;
        $this->orderView     = $orderView;
        $this->organizerView = $organizerView;
        $this->billingView   = $billingView;
    }
}
