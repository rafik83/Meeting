<?php

namespace Proximum\Vimeet\Domain\View\Invoice;

use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;

class ExportView
{
    /** @var int */
    public $eventId;

    /** @var string */
    public $eventTitle;

    /** @var int */
    public $ownerId;

    /** @var string */
    public $sheetTitle;

    /** @var string */
    public $invoiceNumber;

    /** @var int */
    public $total;

    /** @var int */
    public $totalWithVat;

    /** @var int */
    public $vatAmount;

    /** @var int */
    public $balance;

    /** @var string */
    public $billingInfoCountry;

    /** @var null|string */
    public $vatNumber;

    /** @var float */
    public $vatRate;

    /** @var null|string */
    public $analyticsCode;

    /** @var string */
    public $invoiceDate;

    /** @var int */
    public $sheetId;

    /** @var null|VatListView */
    public $vatListView;

    public function __construct(
        int $eventId,
        string $eventTitle,
        int $ownerId,
        int $sheetId,
        string $sheetTitle,
        string $invoiceNumber,
        float $vatRate,
        string $invoiceDate,
        int $total,
        int $totalWithVat,
        int $vatAmount,
        int $balance,
        ?string $analyticsCode,
        ?string $vatNumber,
        string $billingInfoCountry,
        ?VatListView $vatListView
    ) {
        $this->eventId = $eventId;
        $this->eventTitle = $eventTitle;
        $this->ownerId = $ownerId;
        $this->sheetTitle = $sheetTitle;
        $this->invoiceNumber = $invoiceNumber;
        $this->vatRate = $vatRate;
        $this->total = $total;
        $this->totalWithVat = $totalWithVat;
        $this->vatAmount = $vatAmount;
        $this->balance = $balance;
        $this->analyticsCode = $analyticsCode;
        $this->invoiceDate = $invoiceDate;
        $this->vatNumber = $vatNumber;
        $this->billingInfoCountry = $billingInfoCountry;
        $this->sheetId = $sheetId;
        $this->vatListView = $vatListView;
    }
}
