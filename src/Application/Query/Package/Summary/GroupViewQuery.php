<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Sheet;

class GroupViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var PackageGroup
     */
    public $group;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var PlanGroupView
     */
    public $planGroupView;

    /**
     * @param Sheet         $sheet
     * @param PackageGroup  $group
     * @param Cart          $cart
     * @param string        $locale
     * @param PlanGroupView $planGroupView
     */
    public function __construct(Sheet $sheet, PackageGroup $group, Cart $cart, $locale, PlanGroupView $planGroupView = null)
    {
        $this->sheet         = $sheet;
        $this->group         = $group;
        $this->locale        = $locale;
        $this->cart          = $cart;
        $this->planGroupView = $planGroupView;
    }
}
