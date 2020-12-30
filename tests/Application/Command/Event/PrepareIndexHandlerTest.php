<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Event\PrepareIndex;
use Proximum\Vimeet\Application\Command\Event\PrepareIndexHandler;

class PrepareIndexHandlerTest extends TestCase
{
    public function testHandle()
    {
        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexEventFromScratch()->shouldBeCalled();

        $command = new PrepareIndex();
        $handler = new PrepareIndexHandler($jobQueue->reveal());

        $handler->handle($command);
    }
}
