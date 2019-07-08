<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode\Batch;

use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /** @var string */
    public $title;

    /** @var null|string */
    public $prefix;

    /** @var \DateTimeInterface */
    public $validUntil;

    /** @var int */
    public $number = 1;

    /** @var null|int */
    public $stock;

    /** @var array */
    public $translations = [];

    /** @var array */
    public $promotions = [];

    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'label'       => null,
                'description' => null,
            ];
        }
    }
}
