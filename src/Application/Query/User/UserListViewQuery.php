<?php

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Domain\Model\Event;

class UserListViewQuery
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
     * @var int
     */
    public $page;

    /**
     * @var array
     */
    public $filters;

    /**
     * UserListViewQueryHandler constructor.
     *
     * @param Event  $event
     * @param string $locale
     * @param int    $page
     * @param array  $filters
     */
    public function __construct(Event $event, $locale, $page, array $filters)
    {
        $this->event   = $event;
        $this->locale  = $locale;
        $this->page    = $page;
        $this->filters = $filters;
    }
}
