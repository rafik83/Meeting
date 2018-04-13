<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium\ComexposiumJobQueueInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetEventReferenceHandler;

class ScheduleExportHandler
{
    /** @var ComexposiumJobQueueInterface */
    private $jobQueue;

    /** @var GetEventReferenceHandler */
    private $getEventReferenceHandler;

    public function __construct(
        ComexposiumJobQueueInterface $jobQueue,
        GetEventReferenceHandler $getEventReferenceHandler
    ) {
        $this->jobQueue = $jobQueue;
        $this->getEventReferenceHandler = $getEventReferenceHandler;
    }

    public function handle(ScheduleExport $scheduleExport): void
    {
        // Ensure Event has Comexposium reference
        $this->getEventReferenceHandler->handle($scheduleExport->event);

        $this->jobQueue->exportSpot($scheduleExport->event, $scheduleExport->admin);
    }
}
