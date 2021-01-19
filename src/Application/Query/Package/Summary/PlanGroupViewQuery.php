<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;

class PlanGroupViewQuery
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
     * @param Sheet  $sheet
     * @param Cart   $cart
     * @param string $locale
     *
     * @throws \Exception
     */
    public function __construct(Sheet $sheet, Cart $cart, $locale)
    {
        $this->sheet  = $sheet;
        $this->cart   = $cart;
        $this->locale = $locale;
    }
}
