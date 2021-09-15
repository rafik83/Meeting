<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Rooming\Export\PrepareExport;
use Proximum\Vimeet\Application\Command\Rooming\Export\PrepareExportHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PrepareExportHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->exportRoomingList($event->reveal(), $admin->reveal(), 'fr')
            ->shouldBeCalled()
        ;

        $handler = new PrepareExportHandler($jobQueue->reveal());

        $handler->handle(new PrepareExport($event->reveal(), $admin->reveal(), 'fr'));
    }
}
