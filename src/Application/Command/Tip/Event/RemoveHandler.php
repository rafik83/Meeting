<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\RemovedEvent;
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
        $event = $remove->tip->getEvent();

        $this->tipRepository->removeTip($remove->tip);

        if (null !== $event) {
            $this->eventDispatcher->dispatch(
                Events::TIP_REMOVED_FROM_EVENT,
                new RemovedEvent($event)
            );
        }
    }
}
