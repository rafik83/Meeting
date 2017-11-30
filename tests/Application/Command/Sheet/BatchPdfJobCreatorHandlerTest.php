<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfJobCreator;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfJobCreatorHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchPdfJobCreatorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);
        $admin->getEmail()->willReturn('admin@example.net');
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $jobQueue
            ->printSheetsPdf($event->reveal(), [11, 13], 'admin@example.net', 'fr', 'alphabetical')
            ->shouldBeCalled()
        ;

        $command = new BatchPdfJobCreator($event->reveal(), [11, 13], $admin->reveal(), 'fr', 'alphabetical');
        $handler = new BatchPdfJobCreatorHandler($jobQueue->reveal());
        $result = $handler->handle($command);

        $expected = new BatchResult(2, 'flash.admin.sheet_batch.printPdf.success');

        $this->assertEquals($expected, $result);
    }
}
