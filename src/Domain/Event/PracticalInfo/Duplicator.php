<?php

namespace Proximum\Vimeet\Domain\Event\PracticalInfo;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class Duplicator
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * Duplicator constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Event $event
     */
    public function duplicate(Event $event)
    {
        $duplicatedEvent              = $event->getDuplicatedFrom();
        $duplicatedEventConfiguration = $duplicatedEvent->getConfiguration();

        $event->setOrganiserName($duplicatedEvent->getOrganiserName());
        $event->setOrganiserEmail($duplicatedEvent->getOrganiserEmail());
        $event->getConfiguration()->updatePracticalInfo(
            $duplicatedEventConfiguration->getContactFirstName(),
            $duplicatedEventConfiguration->getContactLastName(),
            $duplicatedEventConfiguration->getOrganiserPhone(),
            $duplicatedEventConfiguration->getOrganiserWebsite()
        );

        $this->eventRepository->set($event);
    }
}
