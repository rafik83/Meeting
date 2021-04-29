<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class SearchUser implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    /**
     * SearchUser constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
