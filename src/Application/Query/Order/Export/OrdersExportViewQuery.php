<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Event;

class OrdersExportViewQuery
{
    /** @var Event */
    public $event;

    /** @var string */
    public $adminLocale;

    /**
     * @param Event  $event
     * @param string $adminLocale
     */
    public function __construct(Event $event, $adminLocale)
    {
        $this->event       = $event;
        $this->adminLocale = $adminLocale;
    }
}
