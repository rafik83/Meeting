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
use Proximum\Vimeet\Application\Command\Sheet\BatchValidate;
use Proximum\Vimeet\Application\Command\Sheet\BatchValidateHandler;
use Proximum\Vimeet\Application\Command\Sheet\Validate;
use Proximum\Vimeet\Application\Command\Sheet\ValidateHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchValidateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = new Event();
        $type    = new Type($event);
        $admin   = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', new \DateTime());
        $date    = new \DateTime();
        $comment = 'truc muche';

        $user1 = new User('test@test.com', 'salt', 'password', 'fr');
        $user2 = new User('test@test.com', 'salt', 'password', 'fr');
        $user3 = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $sheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $sheet3 = new Sheet($event, $type, [], $user3, new \DateTime());
        $sheet3->markAsValidated();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $validateHandler = $this->prophesize(ValidateHandler::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);
        $validateHandler->handle(Argument::that(function (Validate $validate) {
            return !$validate->sheet->isValidated();
        }))->shouldBeCalledTimes(2);

        $command = new BatchValidate([1, 2, 3], $admin, $date, $comment);

        $handler = new BatchValidateHandler($sheetRepository->reveal(), $validateHandler->reveal());
        $result  = $handler->handle($command);

        $this->assertEquals(2, $result->count);
    }
}
