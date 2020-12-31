<?php

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Domain\Model\Event;

class CatalogVisibilityQuery
{
    /** @var Event */
    public $event;

    /**
     * CatalogVisibilityQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
