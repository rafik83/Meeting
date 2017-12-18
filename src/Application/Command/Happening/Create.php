<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var Category */
    public $category;

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var array */
    public $translations = [];

    /** @var array */
    public $talkings = [];

    /** @var bool */
    public $questionAllowed;

    /** @var int|null */
    public $limitParticipant;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event         = $event;
        $this->questionAllowed = false;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title'       => '',
                'description' => '',
            ];
        }
    }
}
