<?php

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

class SummaryQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var Order */
    public $order;

    /** @var string */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param Order  $order
     * @param string $locale
     */
    public function __construct(Sheet $sheet, Order $order, $locale)
    {
        $this->sheet  = $sheet;
        $this->order  = $order;
        $this->locale = $locale;
    }
}
