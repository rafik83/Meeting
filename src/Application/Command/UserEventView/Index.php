<?php

namespace Proximum\Vimeet\Application\Command\UserEventView;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Index implements Command
{
    /** @var Event */
    public $event;

    /** @var bool */
    public $removeAllByEvent;

    public function __construct(Event $event, bool $removeAllByEvent = false)
    {
        $this->event = $event;
        $this->removeAllByEvent = $removeAllByEvent;
    }
}
