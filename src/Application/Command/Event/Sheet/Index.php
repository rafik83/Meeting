<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Index implements Command
{
    /** @var Event */
    public $event;

    /** @var bool */
    public $removeAllSheetOfEvent;

    /**
     * @param Event $event
     * @param bool  $removeAllSheetOfEvent
     */
    public function __construct(Event $event, bool $removeAllSheetOfEvent = false)
    {
        $this->event = $event;
        $this->removeAllSheetOfEvent = $removeAllSheetOfEvent;
    }
}
