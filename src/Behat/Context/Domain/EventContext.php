<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
     * @param string|null $eventTitle
     */
    public function createEvent(string $eventTitle = null)
    {
        $event = $this->eventContextProxy->getEventManager()->create($eventTitle);
        $this->eventContextProxy->getStorage()->set('event', $event);
    }

    /**
     * @Given the agenda is open
     */
    public function theAgendaIsOpen()
    {
        $event = $this->eventContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->eventContextProxy->getAccessManager()->openTheAgenda($event);
    }

    /**
     * @Given the meetings are published
     */
    public function theMeetingsArePublished()
    {
        $event = $this->eventContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->eventContextProxy->getAccessManager()->publishMeetings($event);
    }
}
