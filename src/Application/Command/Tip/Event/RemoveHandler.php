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
     * RemoveHandler constructor.
     *
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
        $tip = $this->tipRepository->getByEventAndTip($remove->event, $remove->tip);

        if (null === $tip) {
            throw new TipNotFoundException();
        }

        foreach ($tip->getTypes() as $type) {
            if ($type->getEvent() !== $remove->event) {
                throw new TipNotAffectedOnEventException();
            }
        }

        $this->tipRepository->removeTip($tip);

        $this->eventDispatcher->dispatch(
            Events::TIP_UN_ASSIGNED,
            new UnAssignedEvent($remove->event, $remove->tip)
        );
    }
}
