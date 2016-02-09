<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
*/

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Command\Spot\DeleteBatch;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class DeleteBatchHandler
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

    public function handle(DeleteBatch $deleteBatch)
    {
        $this->spotRepository->removeBatchSpot($deleteBatch->idsSpot, $deleteBatch->event);
    }
}