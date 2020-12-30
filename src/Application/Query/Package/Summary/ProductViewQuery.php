<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

class ProductViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Product
     */
    public $product;

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
     * @param Product       $product
     * @param Cart          $cart
     * @param string        $locale
     * @param PlanGroupView $planGroupView
     */
    public function __construct(Sheet $sheet, Product $product, Cart $cart, $locale, PlanGroupView $planGroupView = null)
    {
        $this->sheet         = $sheet;
        $this->product       = $product;
        $this->cart          = $cart;
        $this->locale        = $locale;
        $this->planGroupView = $planGroupView;
    }
}
