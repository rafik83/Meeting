<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class LocalizationViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $filter;

    /** @var array */
    public $defaultFilters;

    /** @var string */
    public $locale;

    public function __construct(Event $event, string $filter, array $defaultFilters, string $locale)
    {
        $this->event = $event;
        $this->filter = $filter;
        $this->defaultFilters = $defaultFilters;
        $this->locale = $locale;
    }
}
