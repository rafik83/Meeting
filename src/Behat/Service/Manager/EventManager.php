<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class EventManager
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function create(?string $eventTitle = null): Event
    {
        $event = EventFactory::createEvent($eventTitle);
        $event->getConfiguration()->setColors(
            '#4697ff',
            '#4b41d0',
            '#00398C',
            '#4697ff',
            '#4b41d0',
            '#FFFFFF',
            '#2F2F2F',
            '#2F2F2F',
            '#FFFFFF'
        );
        foreach ($event->getLocales() as $locale) {
            if (!$event->getTranslations()->get($locale)) {
                $event->getTranslations()->set($locale, new EventTranslation($event, $locale, ''));
            }
        }

        $this->eventRepository->add($event);

        return $event;
    }

    public function set(Event $event): void
    {
        $this->eventRepository->set($event);
    }

    public function findByDomain(string $eventDomain): ?Event
    {
        return $this->eventRepository->getEventByDomain($eventDomain);
    }

    public function setLocale(Event $event, string $locale): void
    {
        $event->setLocales([$locale], $locale);
    }

    public function setOrganiserName(Event $event, string $organiserName): void
    {
        $event->setOrganiserName($organiserName);
        $this->eventRepository->set($event);
    }

    public function setOrganiserEmail(Event $event, string $organiserEmail): void
    {
        $event->setOrganiserEmail($organiserEmail);
        $this->eventRepository->set($event);
    }
}
