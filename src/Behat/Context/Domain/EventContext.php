<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\EventContextProxyInterface;

class EventContext implements Context
{
    /** @var EventContextProxyInterface */
    private $eventContextProxy;

    /**
     * @param EventContextProxyInterface $eventContextProxy
     */
    public function __construct(EventContextProxyInterface $eventContextProxy)
    {
        $this->eventContextProxy = $eventContextProxy;
    }

    /**
     * @Given /^the event "(?P<eventTitle>[^"]+)" is created$/
     *
     * @param string $eventTitle
     */
    public function createEvent($eventTitle = null)
    {
        $event = $this->eventContextProxy->getEventManager()->create($eventTitle);
        $this->eventContextProxy->getStorage()->set('event', $event);
    }
}
