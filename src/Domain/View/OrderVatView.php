<?php

namespace Proximum\Vimeet\Domain\View;

use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;

class OrderVatView
{
    /** @var string */
    public $numero;

    /** @var null|int */
    public $orderId;

    /** @var int */
    public $sheetId;

    /** @var bool */
    public $isVatApplicable;

    /** @var float */
    public $vatRate;

    /** @var string */
    public $vatMode;

    /** @var string */
    public $currency;

    /** @var bool */
    public $isCancelled;

    /** @var int amount in cents */
    public $totalWithoutVat;

    /** @var int amount in cents */
    public $vatAmount;

    /** @var int amount in cents */
    public $totalWithVat;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var null|Invoice */
    public $invoice;

    /** @var VatListView */
    public $vatListView;

    public function __construct(
        string $numero,
        ?int $orderId,
        int $sheetId,
        bool $isVatApplicable,
        float $vatRate,
        string $vatMode,
        string $currency,
        bool $isCancelled,
        int $totalWithoutVat,
        int $vatAmount,
        int $totalWithVat,
        VatListView $vatListView,
        \DateTimeInterface $createdAt,
        ?Invoice $invoice = null
    ) {
        $this->numero          = $numero;
        $this->orderId         = $orderId;
        $this->sheetId         = $sheetId;
        $this->isVatApplicable = $isVatApplicable;
        $this->vatRate         = $vatRate;
        $this->vatMode         = $vatMode;
        $this->currency        = $currency;
        $this->isCancelled     = $isCancelled;
        $this->totalWithoutVat = $totalWithoutVat;
        $this->vatAmount       = $vatAmount;
        $this->totalWithVat    = $totalWithVat;
        $this->createdAt       = $createdAt;
        $this->invoice         = $invoice;
        $this->vatListView     = $vatListView;
    }

    /**
     * @return bool
     */
    public function hasInvoice(): bool
    {
        return null !== $this->invoice;
    }

    /**
     * @return string
     */
    public function getTotalVatMode(): string
    {
        return true === $this->isVatApplicable ? Event::VAT_MODE_ATI : Event::VAT_MODE_ET;
    }
}
