<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\Exception\Spot\UniqueReferenceViolationException;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class UpdateHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param Update $update
     *
     * @throws UniqueReferenceViolationException
     */
    public function handle(Update $update)
    {
        if ($update->referenceHasChanged() && null !== $this->spotRepository->findByReference($update->spot->getEvent(), $update->reference)) {
            throw new UniqueReferenceViolationException($update->reference);
        }

        $update->spot->update($update->reference, $update->size, $update->meetingCapacity, $update->seatCapacity);

        $this->spotRepository->set($update->spot);
    }

}
