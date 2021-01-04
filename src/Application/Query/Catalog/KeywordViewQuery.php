<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;

class KeywordViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $filter;

    /**
     * @var array
     */
    public $defaultFilters;

    /**
     * @var string
     */
    public $locale;

    /**
     * KeywordViewQuery constructor.
     *
     * @param Event  $event
     * @param string $filter
     * @param array  $defaultFilters
     * @param string $locale
     */
    public function __construct(Event $event, string $filter, array $defaultFilters, string $locale)
    {
        $this->event = $event;
        $this->filter = $filter;
        $this->defaultFilters = $defaultFilters;
        $this->locale = $locale;
    }
}
