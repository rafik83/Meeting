<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

use Proximum\Vimeet\Application\Components\Sheet\Order\OrderView;

class ProformaView
{
    /**
     * @var OrderView
     */
    public $orderView;

    /**
     * ProformaView constructor.
     *
     * @param OrderView $orderView
     */
    public function __construct(OrderView $orderView)
    {
        $this->orderView = $orderView;
    }
}
