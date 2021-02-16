<?php

namespace Proximum\Vimeet\Application\Event\Template\Registration;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\EventDispatcher;

class RegistrationTemplateUpdatedEvent extends EventDispatcher\Event
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
