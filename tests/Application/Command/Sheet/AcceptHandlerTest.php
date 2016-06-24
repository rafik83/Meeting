<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Sheet\Accept;
use Proximum\Vimeet\Application\Command\Sheet\AcceptHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AcceptHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $user    = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet   = new Sheet($event, $type, [], $user, new \DateTime());
        $expectedSheet = clone $sheet;
        $expectedSheet->markAsAccepted();
        $admin   = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', new \DateTime());
        $date    = new \DateTime();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $command = new Accept($sheet, $admin, $date);

        $this->assertFalse($sheet->isAccepted());

        $handler = new AcceptHandler($sheetRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($command);

        $sheetRepository->set(Argument::that(function (Sheet $sheet) {
            return $sheet->isAccepted();
        }))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_ACCEPTED, new SheetAcceptedEvent($expectedSheet, $admin, $date))->shouldBeCalled();

        $this->assertTrue($sheet->isAccepted());
    }
}
