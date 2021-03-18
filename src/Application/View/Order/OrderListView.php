<?php

namespace Proximum\Vimeet\Application\View\Order;

class OrderListView
{
    /** @var int */
    public $id;

    /** @var string */
    public $numero;

    /** @var int */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /** @var string */
    public $sheetType;

    /** @var string */
    public $follower;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var float */
    public $totalWithoutVat;

    /** @var string */
    public $vatMode;

    /** @var string */
    public $currency;

    /** @var bool */
    public $isInvoiced;

    /** @var bool */
    public $isCancelled;

    public function __construct(
        int $id,
        string $numero,
        int $sheetId,
        string $sheetTitle,
        string $sheetType,
        string $follower,
        \DateTimeInterface $createdAt,
        float $totalWithoutVat,
        string $vatMode,
        string $currency,
        bool $isInvoiced,
        bool $isCancelled
    ) {
        $this->id = $id;
        $this->numero = $numero;
        $this->sheetId = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->sheetType = $sheetType;
        $this->follower = $follower;
        $this->createdAt = $createdAt;
        $this->totalWithoutVat = $totalWithoutVat;
        $this->vatMode = $vatMode;
        $this->currency = $currency;
        $this->isInvoiced = $isInvoiced;
        $this->isCancelled = $isCancelled;
    }
}
