<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Template\Form;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\EventDispatcher;

class FormTemplateUpdatedEvent extends EventDispatcher\Event
{
    /** @var Event */
    private $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
