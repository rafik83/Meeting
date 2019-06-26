<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Create extends AbstractEventTip
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->display = Tip::DISPLAY_DEFAULT;

        foreach ($this->event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title' => '',
                'content' => '',
            ];
        }
    }
}
