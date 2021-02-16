<?php

namespace Proximum\Vimeet\Domain\View\Normalizer;

use Proximum\Vimeet\Domain\Model\Event;

class EventMeetingsNormalizerView
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
