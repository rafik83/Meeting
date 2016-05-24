<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Template;

use Proximum\Vimeet\Domain\Model\Event;

class ProductsSelectionTemplate extends AbstractTemplate
{
    /**
     * @param Event              $event
     * @param array              $title
     * @param array              $value
     * @param array              $locales
     * @param string             $fallback
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        $title,
        array $value,
        array $locales,
        $fallback,
        \DateTimeInterface $createdAt
    ) {
        parent::__construct($title, $value, $locales, $fallback, $createdAt);

        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->event->getFallback();
    }
}
