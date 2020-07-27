<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Event;

class Create extends AbstractHappeningCommand
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->questionAllowed = false;
        $this->sidebarAllowed = true;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title' => '',
                'description' => '',
            ];
        }
    }
}
