<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;

class SummaryViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var Funnel */
    public $funnel;

    /** @var Cart */
    public $cart;

    /** @var string */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param Funnel $funnel
     * @param Cart   $cart
     * @param string $locale
     */
    public function __construct(Sheet $sheet, Funnel $funnel, Cart $cart, $locale)
    {
        $this->sheet  = $sheet;
        $this->funnel = $funnel;
        $this->cart   = $cart;
        $this->locale = $locale;
    }
}
