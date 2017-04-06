<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Order;

class OrderViewQuery
{
    /** @var Order */
    public $order;

    /** @var string */
    public $locale;

    /** @var string */
    public $adminLocale;

    /**
     * @param Order  $order
     * @param string $locale
     * @param string $adminLocale
     */
    public function __construct(Order $order, $locale, $adminLocale)
    {
        $this->order       = $order;
        $this->locale      = $locale;
        $this->adminLocale = $adminLocale;
    }
}
