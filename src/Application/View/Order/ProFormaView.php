<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;

class ProFormaView
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var string
     */
    public $legalInfo;

    /**
     * @var string
     */
    public $bankInfo;

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
    public $footer;

    /**
     * @var BillingInfo
     */
    public $billingInfo;

    /**
     * @param Sheet       $sheet
     * @param Order       $order
     * @param BillingInfo $billingInfo
     * @param string      $legalInfo
     * @param string      $bankInfo
     * @param string      $billingAddress
     * @param string      $paymentCondition
     * @param string      $footer
     */
    public function __construct(
        Sheet $sheet,
        Order $order,
        BillingInfo $billingInfo,
        $legalInfo,
        $bankInfo,
        $billingAddress,
        $paymentCondition,
        $footer
    ) {
        $this->sheet            = $sheet;
        $this->order            = $order;
        $this->billingInfo      = $billingInfo;
        $this->legalInfo        = $legalInfo;
        $this->bankInfo         = $bankInfo;
        $this->billingAddress   = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->footer           = $footer;
    }
}
