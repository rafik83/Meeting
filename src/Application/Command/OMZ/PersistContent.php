<?php

namespace Proximum\Vimeet\Application\Command\OMZ;

use Proximum\Vimeet\Domain\Model\Event;

class PersistContent
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
