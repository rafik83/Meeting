<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdate;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdateHandler;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Dispatcher;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\DispatcherHandler;

class ExportHandler
{
    /** @var LockMeetingRequestUpdateHandler */
    private $lockMeetingRequestHandler;

    /** @var DispatcherHandler */
    private $dispatcherHandler;

    /**
     * @param DispatcherHandler $dispatcherHandler
     * @param LockMeetingRequestUpdateHandler $lockMeetingRequestUpdateHandler
     */
    public function __construct(
        DispatcherHandler $dispatcherHandler,
        LockMeetingRequestUpdateHandler $lockMeetingRequestUpdateHandler
    ) {
        $this->lockMeetingRequestHandler = $lockMeetingRequestUpdateHandler;
        $this->dispatcherHandler         = $dispatcherHandler;
    }

    /**
     * @param Export $export
     */
    public function handle(Export $export)
    {
        $this->dispatcherHandler->handle(new Dispatcher($export->event));

        if (true === $export->lockMeetingRequest) {
            $command = new LockMeetingRequestUpdate($export->event);
            $command->lock = true;

            $this->lockMeetingRequestHandler->handle($command);
        }
    }
}
