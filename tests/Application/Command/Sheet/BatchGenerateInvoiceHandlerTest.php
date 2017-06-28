<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchGenerateInvoice;
use Proximum\Vimeet\Application\Command\Sheet\BatchGenerateInvoiceHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchGenerateInvoiceHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $date  = new \DateTime();
        $admin = new Admin('email@email.com', 'test', 'test', 'fr', 'test', 'test', 'ROLE_SUPER_ADMIN', $date);

        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $command = new BatchGenerateInvoice($event, [1], $admin);
        $jobQueue->generateInvoice($event, [1], $admin)->shouldBeCalled();

        $handler = new BatchGenerateInvoiceHandler($jobQueue->reveal());
        $result  = $handler->handle($command);

        $this->assertEquals(1, $result->count);
    }
}
