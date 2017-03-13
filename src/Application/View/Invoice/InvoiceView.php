<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

class InvoiceView
{
    /** @var string */
    private $invoiceNumber;

    /** @var string */
    public $eventTitle;

    /** @var string */
    public $invoiceLogo;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var int in cents */
    private $total;

    /** @var int */
    private $totalWithVat;

    /** @var int */
    private $vatAmount;

    /** @var string 3-letter ISO 4217 currency name */
    private $currency;

    /** @var string */
    private $eventDefaultLocale;

    /** @var string */
    public $eventBillingAddress;

    /** @var string */
    private $eventBankInfo;
    /** @var string */
    private $eventPaymentCondition;

    /** @var string */
    private $eventPaymentFooter;

    /** @var SummaryView */
    public $summaryView;

    /** @var BillingInfosView */
    public $billingInfosView;

    /** @var int in cents */
    public $amountRemainToPay;

    /**
     * @param string             $invoiceNumber
     * @param int                $total
     * @param int                $totalWithVat
     * @param int                $vatAmount
     * @param string             $currency
     * @param string             $eventTitle
     * @param string             $invoiceLogo
     * @param \DateTimeInterface $createdAt
     * @param string             $eventDefaultLocale
     * @param string             $eventBillingAddress
     * @param string             $eventBankInfo
     * @param string             $eventPaymentCondition
     * @param string             $eventPaymentFooter
     * @param SummaryView        $summaryView
     * @param BillingInfosView   $billingInfosView
     * @param float              $amountRemainToPay
     */
    public function __construct(
        $invoiceNumber,
        $total,
        $totalWithVat,
        $vatAmount,
        $currency,
        $eventTitle,
        $invoiceLogo,
        \DateTimeInterface $createdAt,
        $eventDefaultLocale,
        $eventBillingAddress,
        $eventBankInfo,
        $eventPaymentCondition,
        $eventPaymentFooter,
        SummaryView $summaryView,
        BillingInfosView $billingInfosView,
        $amountRemainToPay
    ) {
        $this->eventTitle            = $eventTitle;
        $this->invoiceLogo           = $invoiceLogo;
        $this->createdAt             = $createdAt;
        $this->eventDefaultLocale    = $eventDefaultLocale;
        $this->eventBillingAddress   = $eventBillingAddress;
        $this->eventBankInfo         = $eventBankInfo;
        $this->eventPaymentCondition = $eventPaymentCondition;
        $this->eventPaymentFooter    = $eventPaymentFooter;
        $this->summaryView           = $summaryView;
        $this->billingInfosView      = $billingInfosView;
        $this->amountRemainToPay     = $amountRemainToPay;
        $this->invoiceNumber         = $invoiceNumber;
        $this->total                 = $total;
        $this->totalWithVat          = $totalWithVat;
        $this->vatAmount             = $vatAmount;
        $this->currency              = $currency;
    }
}
