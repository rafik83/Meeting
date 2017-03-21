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

class ExportHandler
{
    /** @var LockMeetingRequestUpdateHandler */
    private $lockMeetingRequestHandler;

    /**
     * @param LockMeetingRequestUpdateHandler $lockMeetingRequestUpdateHandler
     */
    public function __construct(
        LockMeetingRequestUpdateHandler $lockMeetingRequestUpdateHandler
    ) {
        $this->lockMeetingRequestHandler = $lockMeetingRequestUpdateHandler;
    }

    /**
     * @param Export $export
     */
    public function handle(Export $export)
    {
        if (true === $export->lockMeetingRequest) {
            $command = new LockMeetingRequestUpdate($export->event);
            $command->lock = true;

            $this->lockMeetingRequestHandler->handle($command);
        }
    }
}
