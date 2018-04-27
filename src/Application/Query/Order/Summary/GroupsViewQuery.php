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
use Proximum\Vimeet\Domain\Model\Sheet;

class GroupsViewQuery
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
     * @var string
     */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param Order  $order
     * @param string $locale
     */
    public function __construct(Sheet $sheet, Order $order, $locale)
    {
        $this->sheet  = $sheet;
        $this->order  = $order;
        $this->locale = $locale;
    }
}
