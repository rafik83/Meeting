<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class Update
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $locales;

    /**
     * @var string
     */
    public $fallback;

    /**
     * @var array
     */
    public $translations;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->title = $event->getTitle();
        $this->locales = $event->getLocales();
        $this->fallback = $event->getFallback();
        $this->translations = [];

        foreach ($event->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'description' => $translation->getDescription(),
            ];
        }
    }
}
