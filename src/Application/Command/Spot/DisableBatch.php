<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Event;

class DisableBatch
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $ids;

    /**
     * DeleteBatch constructor.
     *
     * @param array $ids
     * @param Event $event
     */
    public function __construct(array $ids, Event $event)
    {
        $this->ids   = $ids;
        $this->event = $event;
    }
}
