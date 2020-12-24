<?php

namespace Proximum\Vimeet\Application\Command\OMZ;

use Proximum\Vimeet\Domain\Model\Event;

class PrepareContent
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
