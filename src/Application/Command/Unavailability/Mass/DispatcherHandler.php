<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Mass\Unavailability\DispatchedEvent;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Domain\Unavailability\TimeSlotDispatcher;

class DispatcherHandler
{
    /** @var TimeSlotDispatcher */
    private $timeSlotDispatcher;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * @param TimeSlotDispatcher              $timeSlotDispatcher
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     */
    public function __construct(
        TimeSlotDispatcher $timeSlotDispatcher,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->timeSlotDispatcher = $timeSlotDispatcher;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @throws UnableToDispatchException
     */
    public function handle(Dispatcher $dispatcher)
    {
        $this->timeSlotDispatcher->dispatchAll($dispatcher->event);

        $this->delayedEventDispatcher->dispatch(
            Events::MASS_UNAVAILABILITY_DISPATCHED,
            new DispatchedEvent($dispatcher->event)
        );
    }
}
