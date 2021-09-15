<?php

namespace Proximum\Vimeet\Application\Query\Package\Vat;

use Proximum\Vimeet\Application\View\Package\Summary\GroupsView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodesView;
use Proximum\Vimeet\Domain\Model\Sheet;

class VatListViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var GroupsView */
    public $groups;

    /** @var PromotionCodesView */
    public $promotionCodes;

    /**
     * @param Sheet              $sheet
     * @param GroupsView         $groups
     * @param PromotionCodesView $promotionCodes
     */
    public function __construct(Sheet $sheet, GroupsView $groups, PromotionCodesView $promotionCodes)
    {
        $this->sheet = $sheet;
        $this->groups = $groups;
        $this->promotionCodes = $promotionCodes;
    }
}
