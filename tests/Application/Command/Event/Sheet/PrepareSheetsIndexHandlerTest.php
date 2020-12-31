<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Event\Sheet\PrepareSheetsIndex;
use Proximum\Vimeet\Application\Command\Event\Sheet\PrepareSheetsIndexHandler;
use Proximum\Vimeet\Domain\Model\Event;

class PrepareSheetsIndexHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexSheetsByEvent($event->reveal(), true)->shouldBeCalled();
        $handler = new PrepareSheetsIndexHandler($jobQueue->reveal());
        $handler->handle(new PrepareSheetsIndex($event->reveal(), true));
    }
}
