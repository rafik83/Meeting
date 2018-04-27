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
use Proximum\Vimeet\Domain\Model\Event;

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
        $event = $this->getEvent();

        $this->eventContextProxy->getAccessManager()->openTheAgenda($event);
    }

    /**
     * @Given /^the catalog is open since "(?P<date>[^"]+)"$/
     *
     * @param string $date
     */
    public function theCatalogIsOpen(string $date)
    {
        $event = $this->eventContextProxy->getStorage()->get('event');

        $openDate = new \DateTime($date);

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->eventContextProxy->getAccessManager()->openCatalog($event, $openDate);
    }

    /**
     * @Given /^the external catalog is open$/
     */
    public function theExternalCatalogIsOpen()
    {
        $event = $this->eventContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->eventContextProxy->getAccessManager()->openExternalCatalog($event);
    }

    /**
     * @Given the meetings are published
     */
    public function theMeetingsArePublished()
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getAccessManager()->publishMeetings($event);
    }

    /**
     * @Given the registration are not open
     */
    public function theRegistrationAreNotOpen()
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getAccessManager()->setRegistrationOpenDate(
            new \Datetime('now + 1 day'), $event
        );
    }

    /**
     * @Given the registration are closed
     */
    public function theRegistrationAreClosed()
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getAccessManager()->setRegistrationCloseDate(
            new \DateTime('2000-01-01 08:00:00'), $event
        );
    }

    /**
     * @Given the registration are open
     */
    public function theRegistrationAreOpen()
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getAccessManager()->setRegistrationOpenDate(
            new \Datetime(), $event
        );
        $this->eventContextProxy->getAccessManager()->setRegistrationCloseDate(
            new \Datetime('now + 1 day'), $event
        );
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return Event
     */
    private function getEvent()
    {
        $event = $this->eventContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        return $event;
    }
}
