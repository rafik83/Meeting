<?php

namespace Proximum\Vimeet\Application\View\Invoice\Vat;

class VatView
{
    /** @var float */
    public $vatRate;

    /** @var string */
    public $vatMode;

    /** @var int in cents */
    public $total;

    /** @var int in cents */
    public $totalVat;

    public function __construct(
        float $vatRate,
        string $vatMode,
        int $total,
        int $totalVat
    ) {
        $this->vatRate = $vatRate;
        $this->vatMode = $vatMode;
        $this->total = $total;
        $this->totalVat = $totalVat;
    }
}
