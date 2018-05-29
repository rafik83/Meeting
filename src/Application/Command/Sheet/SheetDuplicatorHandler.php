<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetDuplicatorHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime = $datetime;
    }

    public function handle(SheetDuplicator $command): void
    {
        foreach ($command->sheets as $sheet) {
            $sheet = Sheet::duplicateSheetFrom($sheet, $command->type, $this->datetime);

            $this->sheetRepository->add($sheet);
            $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($sheet));
        }
    }
}
