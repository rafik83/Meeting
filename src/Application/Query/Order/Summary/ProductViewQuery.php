<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\ProductView;
use Proximum\Vimeet\Domain\Model\Order;

class ProductViewQuery
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
     * @var ProductView
     */
    public $planView;

    /**
     * @param Order            $order
     * @param Order\Row        $row
     * @param string           $locale
     * @param ProductView|null $planView
     */
    public function __construct(Order $order, Order\Row $row, $locale, ProductView $planView = null)
    {
        $this->order    = $order;
        $this->row      = $row;
        $this->locale   = $locale;
        $this->planView = $planView;
    }
}
