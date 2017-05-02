<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Exception\Unavailability\CanNotDeleteUnavailabilityException;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\RemoveUnavailabilityEvent;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RemoveHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * RemoveHandler constructor.
     *
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     * @param DelayedEventDispatcher            $eventDispatcher
     */
    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->eventDispatcher          = $eventDispatcher;
    }

    /**
     * @param Remove $remove
     *
     * @throws CanNotDeleteUnavailabilityException
     */
    public function handle(Remove $remove)
    {
        if (!$remove->unavailability->getParticipant()->getSheet()->attend()) {
            throw new CanNotDeleteUnavailabilityException('The sheet does not attend the event, therefore the unavailability can not be remove');
        }

        $this->unavailabilityRepository->remove($remove->unavailability);

        $this->eventDispatcher->dispatch(
            Events::UNAVAILABILITY_REMOVED,
            new RemoveUnavailabilityEvent($remove->unavailability->getParticipant())
        );
    }
}
