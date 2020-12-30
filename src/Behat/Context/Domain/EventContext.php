<?php

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
     */
    public function createEvent(?string $eventTitle = null)
    {
        $event = $this->eventContextProxy->getEventManager()->create($eventTitle);
        $this->eventContextProxy->getStorage()->set('event', $event);
    }

    /**
     * @Given /^there is an event with domain "(?P<eventDomain>[^"]+)"$/
     */
    public function getEventByDomain(string $eventDomain)
    {
        $event = $this->eventContextProxy->getEventManager()->findByDomain($eventDomain);
        $this->eventContextProxy->getStorage()->set('event', $event);
    }

    /**
     * @Given /^the domain for this event is "(?P<domain>[^"]+)"$/
     */
    public function setEventDomain(string $domain)
    {
        $event = $this->getEvent();
        $event->setDomain($domain);

        $this->eventContextProxy->getEventManager()->set($event);
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
            new \DateTime('now + 1 day'), $event
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
            new \DateTime(), $event
        );
        $this->eventContextProxy->getAccessManager()->setRegistrationCloseDate(
            new \DateTime('now + 1 day'), $event
        );
    }

    /**
     * @Given the happenings are open
     */
    public function thehappeningsAreOpen()
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getAccessManager()->setHappeningsOpenDate(
            new \DateTime('yesterday'), $event
        );
    }

    /**
     * @Given the locale for this event is :locale
     */
    public function theLocaleForThisEventIs($locale)
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getEventManager()->setLocale($event, $locale);
    }

    /**
     * @Given the organiser name of this event is :organiserName
     */
    public function theOrganiserNameOfThisEventIs($organiserName)
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getEventManager()->setOrganiserName($event, $organiserName);
    }

    /**
     * @Given the organiser email of this event is :organiserEmail
     */
    public function theOrganiserEmailOfThisEventIs($organiserEmail)
    {
        $event = $this->getEvent();

        $this->eventContextProxy->getEventManager()->setOrganiserEmail($event, $organiserEmail);
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
