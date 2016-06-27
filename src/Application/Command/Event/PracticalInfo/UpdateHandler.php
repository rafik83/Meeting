<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\PracticalInfo;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UpdateHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * UpdateHandler constructor.
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->event
            ->getConfiguration()
            ->updatePracticalInfo(
                $update->contactFirstName,
                $update->contactLastName,
                $update->phone,
                $update->website
            )
        ;

        $update->event
            ->setOrganiserName($update->organiserName)
            ->setOrganiserEmail($update->organiserEmail)
        ;

        $this->eventRepository->set($update->event);
    }
}