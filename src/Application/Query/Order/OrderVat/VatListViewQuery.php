<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
