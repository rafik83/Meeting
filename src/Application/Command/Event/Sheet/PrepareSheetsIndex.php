<?php

namespace Proximum\Vimeet\Application\Command\Event\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class PrepareSheetsIndex implements Command
{
    /** @var Event */
    public $event;

    /** @var bool */
    public $reset;

    /**
     * @param Event $event
     * @param bool  $reset
     */
    public function __construct(Event $event, bool $reset = false)
    {
        $this->event = $event;
        $this->reset = $reset;
    }
}
