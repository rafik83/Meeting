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
    public $invoiceNumber;

    /** @var string */
    public $eventTitle;

    /** @var string */
    public $invoiceLogo;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var int in cents */
    public $total;

    /** @var int */
    public $totalWithVat;

    /** @var int */
    public $vatAmount;

    /** @var string 3-letter ISO 4217 currency name */
    public $currency;

    /** @var string */
    public $eventDefaultLocale;

    /** @var string */
    public $eventBillingAddress;

    /** @var string */
    public $eventBankInfo;

    /** @var string */
    public $eventPaymentCondition;

    /** @var string */
    public $eventPaymentFooter;

    /** @var SummaryView */
    public $summaryView;

    /** @var BillingInfosView */
    public $billingInfosView;

    /** @var int in cents */
    public $amountRemainToPay;

    /** @var bool */
    public $vatApplicable;

    /** @var string 'ati'|'et' ; See Proximum\Vimeet\Domain\Model\Event VAT_MODE_ATI and VAT_MODE_ET */
    public $vatMode;

    /** @var float */
    public $vatRate;

    /**
     * @param string             $invoiceNumber
     * @param bool               $vatApplicable
     * @param string             $vatMode
     * @param float              $vatRate
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
     * @param int                $amountRemainToPay
     */
    public function __construct(
        $invoiceNumber,
        $vatApplicable,
        $vatMode,
        $vatRate,
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
        $this->invoiceNumber         = $invoiceNumber;
        $this->vatApplicable         = $vatApplicable;
        $this->vatMode               = $vatMode;
        $this->vatRate               = $vatRate;
        $this->total                 = $total;
        $this->totalWithVat          = $totalWithVat;
        $this->vatAmount             = $vatAmount;
        $this->currency              = $currency;
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
    }
}
