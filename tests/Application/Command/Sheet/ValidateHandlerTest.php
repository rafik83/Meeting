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
use Proximum\Vimeet\Application\Command\Sheet\Validate;
use Proximum\Vimeet\Application\Command\Sheet\ValidateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Application\Event\TraceEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ValidateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = new Event();
        $type    = new Type($event);
        $sheet   = new Sheet($event, $type, [], [], new \DateTime());
        $expectedSheet = clone $sheet;
        $expectedSheet->markAsValidated();
        $admin   = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', new \DateTime());
        $date    = new \DateTime();
        $comment = 'truc muche';

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $command = new Validate($sheet, $admin, $date, $comment);

        $this->assertFalse($sheet->isValidated());

        $handler = new ValidateHandler($sheetRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($command);

        $sheetRepository->set(Argument::that(function (Sheet $sheet) {
            return $sheet->isValidated();
        }))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_VALIDATED, new SheetValidatedEvent($sheet, $date, $comment, $admin))->shouldBeCalled();

        $this->assertTrue($sheet->isValidated());
    }
}
