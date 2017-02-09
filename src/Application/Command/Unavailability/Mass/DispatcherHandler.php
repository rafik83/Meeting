<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Domain\Unavailability\TimeSlotDispatcher;

class DispatcherHandler
{
    /**
     * @param TimeSlotDispatcher $timeSlotDispatcher
     */
    public function __construct(TimeSlotDispatcher $timeSlotDispatcher)
    {
        $this->timeSlotDispatcher = $timeSlotDispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @throws UnableToDispatchException
     */
    public function handle(Dispatcher $dispatcher)
    {
        $this->timeSlotDispatcher->dispatchAll($dispatcher->event);
    }
}
