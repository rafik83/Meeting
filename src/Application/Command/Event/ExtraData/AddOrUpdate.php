<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraData;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class AddOrUpdate implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $name;

    /** @var null|string */
    public $value;

    public function __construct(Event $event, string $name, ?string $value)
    {
        $this->event = $event;
        $this->name = $name;
        $this->value = $value;
    }
}
