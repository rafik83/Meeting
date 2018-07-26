<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;

class ScanCommandHandler
{
    /** @var ScanRepositoryInterface */
    private $scanRepository;

    public function __construct(ScanRepositoryInterface $scanRepository)
    {
        $this->scanRepository = $scanRepository;
    }

    public function handle(ScanCommand $command): void
    {
        $this->scanRepository->add(
            new Scan(
                $command->event,
                $command->user,
                $command->scannedAt,
                $command->createdAt
            )
        );
    }
}
