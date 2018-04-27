<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Category;

use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /**
     * @var string
     */
    public $picto;

    /**
     * @var string
     */
    public $title;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event      = $event;
        $this->leftColor  = $event->getConfiguration()->getLeftColor();
        $this->rightColor = $event->getConfiguration()->getRightColor();
    }
}
