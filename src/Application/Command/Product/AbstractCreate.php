<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Domain\Model\Event;

abstract class AbstractCreate
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var int
     */
    public $quantityMax;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = ['title' => null, 'description' => null];
        }
    }
}
