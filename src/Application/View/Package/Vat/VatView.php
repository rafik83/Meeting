<?php

namespace Proximum\Vimeet\Application\View\Package\Vat;

use Proximum\Vimeet\Domain\Money\AmountFormatter;

class VatView
{
    /** @var float */
    public $vatRate;

    /** @var string */
    public $vatMode;

    /** @var int in cents */
    public $total;

    /** @var int the value of the VAT in cents */
    public $totalVat;

    /**
     * @param float  $vatRate
     * @param string $vatMode
     * @param int    $total    in cents
     * @param int    $totalVat in cents
     */
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

    /**
     * @param int $price in cents
     */
    public function addToTotal(int $price): void
    {
        $this->total += $price;
        $this->totalVat = AmountFormatter::calculateRateAmount($this->total, $this->vatRate);
    }
}
