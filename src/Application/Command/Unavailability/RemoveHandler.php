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
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class RemoveHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     */
    public function __construct(UnavailabilityRepositoryInterface $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
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
    }
}
