<?php

namespace Proximum\Vimeet\Application\Command\Schedule;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class ConfigureHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * ConfigureHandler constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Configure $configure
     */
    public function handle(Configure $configure)
    {
        $configure->event->getConfiguration()->setScheduleScale($configure->scale);

        $this->eventRepository->set($configure->event);
    }
}
