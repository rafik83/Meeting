<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class DeleteHandler
{
    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /**
     * @param MassRepositoryInterface $massRepository
     */
    public function __construct(MassRepositoryInterface $massRepository)
    {
        $this->massRepository = $massRepository;
    }

    /**
     * @param Delete $delete
     */
    public function handle(Delete $delete)
    {
        $this->massRepository->remove($delete->mass);
    }
}
