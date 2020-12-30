<?php

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Exception\Event\EventDoesNotHaveLocale;
use Proximum\Vimeet\Domain\Exception\Event\EventNotFoundException;
use Proximum\Vimeet\Domain\Exception\Event\EventNotVisibleException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class EventByHostResolver
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

    /**
     * @param string $host
     * @param string $locale
     *
     * @throws EventDoesNotHaveLocale
     * @throws EventNotFoundException
     * @throws EventNotVisibleException
     *
     * @return Event
     */
    public function resolveEventFromHostAndLocale(string $host, string $locale): Event
    {
        $event = $this->eventRepository->getEventByDomain($host);

        $this->verifyIfEventExistAndIsVisible($event);

        if (!$event->hasLocale($locale)) {
            throw new EventDoesNotHaveLocale('Locale not found', $locale);
        }

        return $event;
    }

    public function resolveEventFromHost(string $host): Event
    {
        $event = $this->eventRepository->getEventByDomain($host);

        $this->verifyIfEventExistAndIsVisible($event);

        return $event;
    }

    private function verifyIfEventExistAndIsVisible(?Event $event): void
    {
        if (!$event instanceof Event) {
            throw new EventNotFoundException('Event not found');
        }

        if (!$event->isVisible()) {
            throw new EventNotVisibleException('Event not visible');
        }
    }
}
