<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Domain\Model\Order;

class PromotionCodesViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Order
     */
    public $order;

    /**
     * @param Order  $order
     * @param string $locale
     */
    public function __construct(Order $order, $locale)
    {
        $this->order  = $order;
        $this->locale = $locale;
    }
}
