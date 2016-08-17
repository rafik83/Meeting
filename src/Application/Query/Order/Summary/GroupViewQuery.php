<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\RowView;
use Proximum\Vimeet\Domain\Model\Order;

class GroupViewQuery
{
    /**
     * @var Order
     */
    public $order;

    /**
     * @var null|int
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
     * @var null|RowView
     */
    public $planView;

    /**
     * @param Order        $order
     * @param string       $locale
     * @param string       $type
     * @param null|int     $groupId
     * @param null|RowView $planView
     */
    public function __construct(Order $order, $locale, $type, $groupId = null, RowView $planView = null)
    {
        $this->order    = $order;
        $this->locale   = $locale;
        $this->type     = $type;
        $this->groupId  = $groupId;
        $this->planView = $planView;
    }
}
