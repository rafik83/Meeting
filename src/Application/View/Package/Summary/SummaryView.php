<?php

namespace Proximum\Vimeet\Application\View\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;

class SummaryView
{
    /** @var GroupsView */
    public $groups;

    /** @var PromotionCodesView */
    public $promotionCodes;

    /** @var string */
    public $vatMode;

    /** @var int in cents */
    public $total;

    /** @var int in cents */
    public $totalPlusVat;

    /** @var string */
    public $currency;

    /** @var Funnel */
    public $funnel;

    /** @var bool */
    public $mustPayVat;

    /** @var VatListView */
    public $vatListView;

    public function __construct(
        Funnel $funnel,
        GroupsView $groupsView,
        PromotionCodesView $promotionCodesView,
        string $vatMode,
        int $total,
        int $totalPlusVat,
        string $currency,
        bool $mustPayVat,
        VatListView $vatListView
    ) {
        $this->funnel = $funnel;
        $this->groups = $groupsView;
        $this->vatMode = $vatMode;
        $this->total = $total;
        $this->mustPayVat = $mustPayVat;
        $this->currency = $currency;
        $this->promotionCodes = $promotionCodesView;
        $this->totalPlusVat = $totalPlusVat;
        $this->vatListView = $vatListView;
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return 0 === \count($this->funnel->getCart()->getRows());
    }
}
