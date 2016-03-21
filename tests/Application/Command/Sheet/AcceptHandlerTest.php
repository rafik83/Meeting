<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Sheet;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Sheet\Accept;
use Proximum\Vimeet\Application\Command\Sheet\AcceptHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\TraceEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AcceptHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = new Event();
        $type    = new Type($event);
        $sheet   = new Sheet($event, $type, [], [], new \DateTime());
        $expectedSheet = clone $sheet;
        $expectedSheet->markAsAccepted();
        $admin   = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', new \DateTime());
        $date    = new \DateTime();
        $comment = 'truc muche';

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $command = new Accept($sheet, $admin, $date);

        $this->assertFalse($sheet->isAccepted());

        $handler = new AcceptHandler($sheetRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($command);

        $sheetRepository->set(Argument::that(function (Sheet $sheet) {
            return $sheet->isAccepted();
        }))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::TRACE_ACTION, new TraceEvent($expectedSheet, 'accept', $admin, $date, ''))->shouldBeCalled();

        $this->assertTrue($sheet->isAccepted());
    }
}
