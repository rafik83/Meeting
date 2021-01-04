<?php

namespace Proximum\Vimeet\Application\Command\Event\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Event;

class GenerateVersions
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
