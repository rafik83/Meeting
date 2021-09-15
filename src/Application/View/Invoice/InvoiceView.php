<?php

namespace Proximum\Vimeet\Application\View\Invoice;

use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;

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

    /** @var null|string */
    public $eventBillingAddress;

    /** @var null|string */
    public $eventBankInfo;

    /** @var null|string */
    public $eventPaymentCondition;

    /** @var null|string */
    public $eventPaymentFooter;

    /** @var SummaryView */
    public $summaryView;

    /** @var BillingInfosView */
    public $billingInfosView;

    /** @var VatListView */
    public $vatListView;

    /** @var int in cents */
    public $amountRemainToPay;

    /** @var bool */
    public $vatApplicable;

    /** @var string 'ati'|'et' ; See Proximum\Vimeet\Domain\Model\Event VAT_MODE_ATI and VAT_MODE_ET */
    public $vatMode;

    /** @var float */
    public $vatRate;

    /** @var string */
    public $eventTimeZone;

    public function __construct(
        string $invoiceNumber,
        bool $vatApplicable,
        string $vatMode,
        float $vatRate,
        int $total,
        int $totalWithVat,
        int $vatAmount,
        string $currency,
        string $eventTitle,
        ?string $invoiceLogo,
        \DateTimeInterface $createdAt,
        string $eventDefaultLocale,
        string $eventTimeZone,
        ?string $eventBillingAddress,
        ?string $eventBankInfo,
        ?string $eventPaymentCondition,
        ?string $eventPaymentFooter,
        SummaryView $summaryView,
        BillingInfosView $billingInfosView,
        ?VatListView $vatListView,
        int $amountRemainToPay
    ) {
        $this->invoiceNumber = $invoiceNumber;
        $this->vatApplicable = $vatApplicable;
        $this->vatMode = $vatMode;
        $this->vatRate = $vatRate;
        $this->total = $total;
        $this->totalWithVat = $totalWithVat;
        $this->vatAmount = $vatAmount;
        $this->currency = $currency;
        $this->eventTitle = $eventTitle;
        $this->invoiceLogo = $invoiceLogo;
        $this->createdAt = $createdAt;
        $this->eventDefaultLocale = $eventDefaultLocale;
        $this->eventTimeZone = $eventTimeZone;
        $this->eventBillingAddress = $eventBillingAddress;
        $this->eventBankInfo = $eventBankInfo;
        $this->eventPaymentCondition = $eventPaymentCondition;
        $this->eventPaymentFooter = $eventPaymentFooter;
        $this->summaryView = $summaryView;
        $this->billingInfosView = $billingInfosView;
        $this->vatListView = $vatListView;
        $this->amountRemainToPay = $amountRemainToPay;
    }
}
