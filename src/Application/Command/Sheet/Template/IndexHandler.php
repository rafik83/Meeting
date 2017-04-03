<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\SheetTemplate\SheetTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IndexHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Index $index
     */
    public function handle(Index $index)
    {
        $this->eventDispatcher->dispatch(
            Events::SHEET_TEMPLATE_UPDATED,
            new SheetTemplateUpdatedEvent(
                $this->sheetRepository->getBySheetTemplate($index->template)
            )
        );
    }
}
