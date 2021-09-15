<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class SummaryView
{
    /** @var GroupsView */
    public $groupsView;

    /** @var PromotionCodesView */
    public $promotionCodesView;

    /** @var string */
    public $vatMode;

    /** @var string */
    public $currency;

    /**
     * @param GroupsView         $groupsView
     * @param PromotionCodesView $promotionCodesView
     * @param string             $vatMode
     * @param string             $currency
     */
    public function __construct(GroupsView $groupsView, PromotionCodesView $promotionCodesView, $vatMode, $currency)
    {
        $this->groupsView         = $groupsView;
        $this->promotionCodesView = $promotionCodesView;
        $this->vatMode            = $vatMode;
        $this->currency           = $currency;
    }
}
