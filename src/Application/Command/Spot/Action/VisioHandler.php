<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot\Action;

use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class VisioHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param Visio $visio
     */
    public function handle(Visio $visio)
    {
        $this->spotRepository->set($visio->spot->goToVisio());
    }
}
