<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $title;

    /**
     * Create constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event = null)
    {
        $this->event = $event;
    }
}
