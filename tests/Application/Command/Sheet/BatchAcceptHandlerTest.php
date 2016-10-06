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
use Proximum\Vimeet\Application\Command\Sheet\BatchAccept;
use Proximum\Vimeet\Application\Command\Sheet\BatchAcceptHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchAcceptHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $date  = new \DateTime();
        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);
        $type  = new Type($event);
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);
        $sheet3->markAsAccepted();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $acceptHandler = $this->prophesize(AcceptHandler::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);
        $acceptHandler->handle(Argument::that(function (Accept $accept) {
            return !$accept->sheet->isAccepted();
        }))->shouldBeCalledTimes(2);

        $command = new BatchAccept([1, 2, 3], $admin);

        $handler = new BatchAcceptHandler($sheetRepository->reveal(), $acceptHandler->reveal(), $date);
        $result  = $handler->handle($command);

        $this->assertEquals(2, $result->count);
    }
}
