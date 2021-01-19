<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Query;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

final class HasEventReferenceQuery implements Query
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
