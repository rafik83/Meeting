<?php

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class CatalogVisibilityRegistrationUrlQuery implements Query
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
