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
use Proximum\Vimeet\Domain\Model\Sheet;

class GroupViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

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
     * @var null|ProductView
     */
    public $planView;

    /**
     * @param Sheet            $sheet
     * @param Order            $order
     * @param string           $locale
     * @param string           $type
     * @param int|null         $groupId
     * @param ProductView|null $planView
     */
    public function __construct(
        Sheet $sheet,
        Order $order,
        $locale,
        $type,
        $groupId = null,
        ProductView $planView = null
    ) {
        $this->sheet    = $sheet;
        $this->order    = $order;
        $this->locale   = $locale;
        $this->type     = $type;
        $this->groupId  = $groupId;
        $this->planView = $planView;
    }
}
