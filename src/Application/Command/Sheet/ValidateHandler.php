<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Application\Event\TraceEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ValidateHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Validate $validate
     */
    public function handle(Validate $validate)
    {
        if ($validate->sheet->isValidated()) {
            return;
        }

        $this->sheetRepository->set($validate->sheet->markAsValidated());
        $this->eventDispatcher->dispatch(Events::SHEET_VALIDATED, new SheetValidatedEvent($validate->sheet));
        $this->eventDispatcher->dispatch(
            Events::TRACE_ACTION,
            new TraceEvent(
                $validate->sheet,
                'validate',
                $validate->admin,
                $validate->date,
                $validate->comment
            )
        );
    }
}
