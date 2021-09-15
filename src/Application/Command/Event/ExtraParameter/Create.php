<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $type;

    /** @var string */
    public $name;

    /** @var string */
    public $value;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
