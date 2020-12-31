<?php

namespace Proximum\Vimeet\Application\View\Order;

use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SummaryView
{
    /** @var GroupsView */
    public $groups;

    /** @var PromotionCodesView */
    public $promotionCodes;

    /** @var float */
    public $total;

    /** @var float */
    public $vatRate;

    /** @var bool */
    public $vatApplicable;

    /** @var string */
    public $vatMode;

    /** @var string */
    public $currency;

    /** @var Sheet */
    public $sheet;

    /** @var float */
    public $vatAmount;

    /** @var float */
    public $totalWithoutVat;

    /** @var float */
    public $totalWithVat;

    /** @var float */
    public $totalPlusVat;

    /** @var float */
    public $remainingToPay;

    /** @var VatListView */
    public $vatListView;

    /**
     * @param GroupsView         $groups
     * @param PromotionCodesView $promotionCodes
     * @param bool               $vatApplicable
     * @param float              $vatRate
     * @param float              $vatAmount
     * @param string             $vatMode
     * @param float              $totalWithoutVat
     * @param float              $totalWithVat
     * @param VatListView        $vatListView
     * @param string             $currency
     * @param float              $remainingToPay
     * @param Sheet              $sheet
     */
    public function __construct(
        GroupsView $groups,
        PromotionCodesView $promotionCodes,
        $vatApplicable,
        $vatRate,
        $vatAmount,
        $vatMode,
        $totalWithoutVat,
        $totalWithVat,
        VatListView $vatListView,
        $currency,
        $remainingToPay,
        Sheet $sheet
    ) {
        $this->groups          = $groups;
        $this->promotionCodes  = $promotionCodes;
        $this->vatApplicable   = $vatApplicable;
        $this->vatRate         = $vatRate;
        $this->vatAmount       = $vatAmount;
        $this->vatMode         = $vatMode;
        $this->totalWithoutVat = $totalWithoutVat;
        $this->totalWithVat    = $totalWithVat;
        $this->vatListView     = $vatListView;
        $this->currency        = $currency;
        $this->sheet           = $sheet;
        $this->remainingToPay  = $remainingToPay;
    }

    /**
     * @return string
     */
    public function getTotalVatMode()
    {
        return true === $this->vatApplicable ? Event::VAT_MODE_ATI : Event::VAT_MODE_ET;
    }
}
