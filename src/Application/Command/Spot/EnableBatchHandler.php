<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class EnableBatchHandler
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

    /**
     * @param EnableBatch $enableBatch
     */
    public function handle(EnableBatch $enableBatch)
    {
        $this->spotRepository->enableBatchSpot($enableBatch->ids, $enableBatch->event);
    }
}
