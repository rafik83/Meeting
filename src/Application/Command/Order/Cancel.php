<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Order;

class Cancel implements Command
{
    /** @var Order */
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
