<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium\ComexposiumJobQueueInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\Exception\EventHasNotComexposiumReferenceException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetEventReferenceHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\ScheduleExport;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\ScheduleExportHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class ScheduleExportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $jobQueue = $this->prophesize(ComexposiumJobQueueInterface::class);
        $jobQueue->exportSpot($event->reveal(), $admin->reveal())->shouldBeCalled();

        $getEventReferenceHandler = $this->prophesize(GetEventReferenceHandler::class);
        $getEventReferenceHandler->handle($event->reveal())->shouldBeCalled()->willReturn('whatever-reference');

        $scheduleExportHandler = new ScheduleExportHandler($jobQueue->reveal(), $getEventReferenceHandler->reveal());
        $scheduleExportHandler->handle(new ScheduleExport($event->reveal(), $admin->reveal()));
    }

    public function testEventHasNotComexposiumReferenceException()
    {
        $this->expectException(EventHasNotComexposiumReferenceException::class);

        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $jobQueue = $this->prophesize(ComexposiumJobQueueInterface::class);
        $jobQueue->exportSpot($event->reveal(), $admin->reveal())->shouldNotBeCalled();

        $getEventReferenceHandler = $this->prophesize(GetEventReferenceHandler::class);
        $getEventReferenceHandler
            ->handle($event->reveal())
            ->shouldBeCalled()
            ->willThrow(EventHasNotComexposiumReferenceException::class)
        ;

        $scheduleExportHandler = new ScheduleExportHandler($jobQueue->reveal(), $getEventReferenceHandler->reveal());
        $scheduleExportHandler->handle(new ScheduleExport($event->reveal(), $admin->reveal()));
    }
}
