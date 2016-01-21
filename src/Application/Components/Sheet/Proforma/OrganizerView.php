<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

class OrganizerView
{
    /**
     * @var string
     */
    public $organiserName;

    /**
     * @var string
     */
    public $paymentAddress;

    /**
     * @var string
     */
    public $organiserEmail;

    /**
     * @var string
     */
    public $bankInfo;

    /**
     * @var string
     */
    public $legalInformation;

    /**
     * @var string
     */
    public $elementToJoinWithInvoice;

    /**
     * OrganizerView constructor.
     *
     * @param string $organiserName
     * @param string $paymentAddress
     * @param string $organiserEmail
     * @param string $bankInfo
     * @param string $legalInformation
     * @param string $elementToJoinWithInvoice
     */
    public function __construct($organiserName, $paymentAddress, $organiserEmail, $bankInfo, $legalInformation, $elementToJoinWithInvoice)
    {
        $this->organiserName            = $organiserName;
        $this->paymentAddress           = $paymentAddress;
        $this->organiserEmail           = $organiserEmail;
        $this->bankInfo                 = $bankInfo;
        $this->legalInformation         = $legalInformation;
        $this->elementToJoinWithInvoice = $elementToJoinWithInvoice;
    }
}
