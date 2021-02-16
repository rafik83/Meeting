<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;

class Affect
{
    /** @var Event */
    public $event;

    /** @var Tip */
    public $tip;

    /** @var Type[] */
    public $types;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
