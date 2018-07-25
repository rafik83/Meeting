<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;

class ScanCommandHandler
{
    /** @var EntityManagerAdapterInterface */
    private $entityManagerAdapter;

    public function __construct(EntityManagerAdapterInterface $entityManagerAdapter)
    {
        $this->entityManagerAdapter = $entityManagerAdapter;
    }

    public function handle(ScanCommand $command): void
    {
        $scan = new Scan(
            $command->event,
            $command->user,
            $command->scannedAt,
            $command->createdAt
        );

        $this->entityManagerAdapter->persist($scan);
        $this->entityManagerAdapter->flush($scan);
    }
}
