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

class ScheduleExportHandler
{
    /** @var ComexposiumJobQueueInterface */
    private $jobQueue;

    public function __construct(ComexposiumJobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    public function handle(ScheduleExport $command): void
    {
        $this->jobQueue->exportSpot($command->event, $command->admin);
    }
}
