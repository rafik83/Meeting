<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var int[] */
    public $sheetIds;

    /** @var string */
    public $name;

    /** @var array */
    public $filters;

    /**
     * @param Event $event
     * @param array $filters
     */
    public function __construct(Event $event, array $filters)
    {
        $this->event   = $event;
        $this->filters = $filters;
    }
}
