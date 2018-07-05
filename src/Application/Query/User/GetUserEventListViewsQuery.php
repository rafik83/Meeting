<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetUserEventListViewsQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $page;

    /** @var string */
    public $locale;

    public function __construct(Event $event, int $page, string $locale)
    {
        $this->event = $event;
        $this->page = $page;
        $this->locale = $locale;
    }
}
