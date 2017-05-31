<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Remove
{
    /** @var Event */
    public $event;

    /** @var Tip */
    public $tip;

    /**
     * Remove constructor.
     *
     * @param Event $event
     * @param Tip   $tip
     */
    public function __construct(Event $event, Tip $tip)
    {
        $this->event = $event;
        $this->tip   = $tip;
    }
}
