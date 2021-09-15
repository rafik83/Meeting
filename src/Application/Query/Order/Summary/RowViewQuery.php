<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\RowView;
use Proximum\Vimeet\Domain\Model\Order;

class RowViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Order\Row
     */
    public $row;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var RowView
     */
    public $planView;

    /**
     * @param Order        $order
     * @param Order\Row    $row
     * @param string       $locale
     * @param null|RowView $planView
     */
    public function __construct(Order $order, Order\Row $row, $locale, RowView $planView = null)
    {
        $this->order    = $order;
        $this->row      = $row;
        $this->locale   = $locale;
        $this->planView = $planView;
    }
}
