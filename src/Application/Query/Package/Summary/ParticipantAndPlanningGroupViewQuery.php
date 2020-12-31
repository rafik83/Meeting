<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantAndPlanningGroupViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

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
     * @param Cart          $cart
     * @param string        $locale
     * @param PlanGroupView $planGroupView
     */
    public function __construct(Sheet $sheet, Cart $cart, $locale, PlanGroupView $planGroupView = null)
    {
        $this->sheet         = $sheet;
        $this->cart          = $cart;
        $this->locale        = $locale;
        $this->planGroupView = $planGroupView;
    }
}
