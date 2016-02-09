<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
*/

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class DisableBatchHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * DeleteBatchHandler constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    public function handle(DisableBatch $disableBatch)
    {
        $this->spotRepository->disableBatchSpot($disableBatch->idsSpot, $disableBatch->event);
    }
}