<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class PersistContent implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $content;

    public function __construct(Event $event, string $content)
    {
        $this->event = $event;
        $this->content = $content;
    }
}
