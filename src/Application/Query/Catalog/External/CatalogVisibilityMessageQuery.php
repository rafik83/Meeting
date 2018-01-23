<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Domain\Model\Event;

class CatalogVisibilityMessageQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * CatalogVisibilityMessageQuery constructor.
     *
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, string $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;
    }
}
