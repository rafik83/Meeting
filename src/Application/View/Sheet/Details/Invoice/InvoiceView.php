<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Invoice;

class InvoiceView
{
    /** @var int */
    public $id;

    /** @var string */
    public $number;

    /** @var int in cents */
    public $total;

    /** @var int in cents */
    public $totalWithVat;

    /** @var string */
    public $currency;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var string */
    public $url;

    /** @var OrderView[] */
    public $orderViews;

    /**
     * @param int                $id
     * @param string             $number
     * @param int                $total        in cents
     * @param int                $totalWithVat in cents
     * @param string             $currency
     * @param \DateTimeInterface $createdAt
     * @param OrderView[]        $orderViews
     * @param string             $url
     */
    public function __construct(
        $id,
        $number,
        $total,
        $totalWithVat,
        $currency,
        \DateTimeInterface $createdAt,
        $orderViews,
        $url
    ) {
        $this->id           = $id;
        $this->number       = $number;
        $this->total        = $total;
        $this->totalWithVat = $totalWithVat;
        $this->currency     = $currency;
        $this->createdAt    = $createdAt;
        $this->url          = $url;
        $this->orderViews   = $orderViews;
    }
}
