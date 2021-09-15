<?php

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Order;

class VatListViewQuery implements Query
{
    /** @var Order */
    public $order;

    /** @var bool */
    public $isVatApplicable;

    public function __construct(Order $order, bool $isVatApplicable)
    {
        $this->order = $order;
        $this->isVatApplicable = $isVatApplicable;
    }
}
