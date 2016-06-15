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
use Proximum\Vimeet\Application\Command\Sheet\BatchAssign;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssignHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAssignHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event    = new Event();
        $type     = new Type($event);
        $dateTime = new \DateTime();
        $user1    = new User('test@test.com', 'salt', 'password', 'fr');
        $user2    = new User('test@test.com', 'salt', 'password', 'fr');
        $user3    = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1   = new Sheet($event, $type, [], $user1, $dateTime);
        $sheet2   = new Sheet($event, $type, [], $user2, $dateTime);
        $sheet3   = new Sheet($event, $type, [], $user3, $dateTime);

        $organizer = new Admin('test@test.com', '', '', 'fr', 'Test', 'Test', Admin::ROLE_ORGANIZER, $dateTime);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        $sheetRepository->set(Argument::that(function (Sheet $sheet) use ($organizer) {
            return $sheet->getFollower() === $organizer;
        }))->shouldBeCalledTimes(3);

        $command = new BatchAssign([1, 2, 3], $organizer);

        $handler = new BatchAssignHandler($sheetRepository->reveal());
        $result  = $handler->handle($command);

        $this->assertEquals(3, $result->count);
    }
}
