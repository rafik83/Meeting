<?php

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class TypeViewQuery implements Query
{
    /**
     * @var int
     */
    public $page;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * TypeViewQuery constructor.
     *
     * @param int    $page
     * @param Event  $event
     * @param string $locale
     */
    public function __construct($page, $event, $locale)
    {
        $this->page   = $page;
        $this->event  = $event;
        $this->locale = $locale;
    }
}
