<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Model\Event;

class CatalogTypeViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $filters;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     */
    public function __construct(Event $event, array $filters, $locale)
    {
        $this->event   = $event;
        $this->filters = $filters;
        $this->locale  = $locale;
    }
}
