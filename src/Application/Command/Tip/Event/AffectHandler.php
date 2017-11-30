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
use Proximum\Vimeet\Application\Event\Tip\AssignedEvent;
use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class AffectHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * AffectHandler constructor.
     *
     * @param TipRepositoryInterface          $tipRepository
     * @param TypeRepositoryInterface         $typeRepository
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        TypeRepositoryInterface $typeRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->tipRepository  = $tipRepository;
        $this->typeRepository = $typeRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Affect $affect
     *
     * @throws TipNotFoundException
     */
    public function handle(Affect $affect)
    {
        $tip = $this->tipRepository->getById($affect->tip->id);

        if (null === $tip) {
            throw new TipNotFoundException();
        }

        foreach ($affect->types as $typeView) {
            $type = $this->typeRepository->getById($typeView->id);
            $tip->setType($type);
        }

        $this->tipRepository->set($tip);

        $this->delayedEventDispatcher->dispatch(Events::TIP_ASSIGNED, new AssignedEvent($affect->event, $tip));
    }
}
