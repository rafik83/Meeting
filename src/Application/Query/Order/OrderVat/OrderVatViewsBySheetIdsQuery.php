<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Model\Event;

class OrderVatViewsBySheetIdsQuery
{
    /** @var Event */
    public $event;

    /** @var int[] */
    public $sheetIds;

    /**
     * @param Event $event
     * @param array $sheetIds
     */
    public function __construct(Event $event, array $sheetIds)
    {
        $this->event = $event;
        $this->sheetIds = $sheetIds;
    }
}
