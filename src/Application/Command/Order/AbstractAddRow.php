<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Order;

abstract class AbstractAddRow implements Command
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
