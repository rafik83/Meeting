<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetSheetIdsByFiltersQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var string */
    public $locale;

    public function __construct(Event $event, array $filters, string $locale)
    {
        $this->event = $event;
        $this->filters = $filters;
        $this->locale = $locale;
    }
}
