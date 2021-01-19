<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;

class GroupsViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param Cart   $cart
     * @param string $locale
     */
    public function __construct(Sheet $sheet, Cart $cart, $locale)
    {
        $this->sheet  = $sheet;
        $this->cart   = $cart;
        $this->locale = $locale;
    }
}
