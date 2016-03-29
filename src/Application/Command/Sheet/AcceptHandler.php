<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AcceptHandler
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
     * AcceptHandler constructor.
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
     * @param Accept $accept
     */
    public function handle(Accept $accept)
    {
        if ($accept->sheet->isAccepted()) {
            return;
        }

        $this->sheetRepository->set($accept->sheet->markAsAccepted());

        $this->eventDispatcher->dispatch(
            Events::SHEET_ACCEPTED,
            new SheetAcceptedEvent(
                $accept->sheet,
                $accept->admin,
                $accept->date
            )
        );
    }
}
