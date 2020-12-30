<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order;

abstract class AbstractAddRow
{
    /** @var Order */
    public $order;

    /** @var string */
    public $label;

    /** @var float */
    public $price;

    /** @var int */
    public $quantity = 1;
}
