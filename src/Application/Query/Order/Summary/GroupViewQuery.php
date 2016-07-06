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

class GroupViewQuery
{
    /**
     * @var Order
     */
    public $order;

    /**
     * @var int
     */
    public $groupId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var ProductView
     */
    public $planView;

    /**
     * @param Order            $order
     * @param string           $locale
     * @param string           $type
     * @param int|null         $groupId
     * @param ProductView|null $planView
     */
    public function __construct(Order $order, $locale, $type, $groupId = null, ProductView $planView = null)
    {
        $this->order    = $order;
        $this->locale   = $locale;
        $this->type     = $type;
        $this->groupId  = $groupId;
        $this->planView = $planView;
    }
}
