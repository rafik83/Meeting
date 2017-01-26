<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Event;

class DeleteBatch
{
    const SPOT_WITH_SHEETS  = 'spotWithSheets';
    const SPOT_WITH_MEETING = 'spotWithMeeting';

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
