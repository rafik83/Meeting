<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\UnAssignedEvent;
use Proximum\Vimeet\Application\Exception\Tip\TipNotAffectedOnEventException;
use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class RemoveHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param TipRepositoryInterface          $tipRepository
     * @param DelayedEventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->tipRepository = $tipRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Remove $remove
     *
     * @throws TipNotAffectedOnEventException
     * @throws TipNotFoundException
     */
    public function handle(Remove $remove)
    {
        $this->tipRepository->removeTip($remove->tip);

        $this->eventDispatcher->dispatch(
            Events::TIP_UN_ASSIGNED,
            new UnAssignedEvent($remove->event, $remove->tip)
        );
    }
}
