<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param string $eventTitle
     *
     * @return Event
     */
    public function create($eventTitle = null): Event
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
}
