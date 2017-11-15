<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\Event\TipView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\TypeView;

class Affect
{
    /** @var Event */
    public $event;

    /** @var TipView */
    public $tip;

    /** @var TypeView[] */
    public $types;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
