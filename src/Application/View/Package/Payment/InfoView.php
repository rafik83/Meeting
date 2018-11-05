<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Payment;

class InfoView
{
    /** @var string */
    public $organiserName;

    /** @var string */
    public $billingAddress;

    /** @var string */
    public $organiserEmail;

    /** @var string */
    public $bankInfo;

    /** @var string */
    public $paymentCondition;

    public function __construct(
        string $organiserName,
        string $billingAddress,
        string $organiserEmail,
        string $bankInfo,
        string $paymentCondition
    ) {
        $this->organiserName = $organiserName;
        $this->billingAddress = $billingAddress;
        $this->organiserEmail = $organiserEmail;
        $this->bankInfo = $bankInfo;
        $this->paymentCondition = $paymentCondition;
    }
}
