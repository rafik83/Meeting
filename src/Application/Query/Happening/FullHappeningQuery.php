<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Event;

class FullHappeningQuery
{
    /**
     * @var ProgramView
     */
    public $programView;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param ProgramView $programView
     * @param Event       $event
     */
    public function __construct(ProgramView $programView, Event $event)
    {
        $this->programView = $programView;
        $this->event       = $event;
    }
}
