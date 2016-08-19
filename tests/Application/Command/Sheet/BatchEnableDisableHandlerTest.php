<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchEnableDisableHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $admin   = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', new \DateTime());

        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $sheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $sheet3 = new Sheet($event, $type, [], $user3, new \DateTime());

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        foreach ([$sheet1, $sheet2, $sheet3] as $sheet) {
            $sheetRepository->set($sheet)->shouldBeCalled();
            $eventDispatcher->dispatch(Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent($sheet, $admin, new \DateTime(), false)
            )->shouldBeCalled();
        }

        $command = new BatchEnableDisable([1, 2, 3], false, $admin);
        $handler = new BatchEnableDisableHandler($sheetRepository->reveal(), $eventDispatcher->reveal());

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
    }
}
