<?php

namespace Proximum\Vimeet\Tests\Application\Command\OMZ;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\OMZ\ScheduleExport;
use Proximum\Vimeet\Application\Command\OMZ\ScheduleExportHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class ScheduleExportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->exportOmzUser($event->reveal(), $admin->reveal())->shouldBeCalled();

        $command = new ScheduleExport($event->reveal(), $admin->reveal());
        $handler = new ScheduleExportHandler($jobQueue->reveal());
        $handler->handle($command);
    }
}
