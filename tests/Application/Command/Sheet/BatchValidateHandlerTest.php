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
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchValidate;
use Proximum\Vimeet\Application\Command\Sheet\BatchValidateHandler;
use Proximum\Vimeet\Application\Command\Sheet\Validate;
use Proximum\Vimeet\Application\Command\Sheet\ValidateHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchValidateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $date    = new \DateTime();
        $admin   = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);
        $comment = 'truc muche';

        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet1->markAsValidated();
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet2->markAsValidated();
        $sheet3 = new Sheet($event, $type, [], $user3, $date);
        $sheet3->markAsValidated();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $batchJobQueue   = $this->prophesize(BatchJobQueueInterface::class);

        $sheetRepository->getUnvalidatedSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2]);

        $sheetRepository->updateStateBySheetsId([1, 2, 3], Sheet::STATE_VALIDATED)->shouldBeCalled();

        $batchJobQueue->createJob(
            [1, 2, 3],
            $admin,
            ['comment' => $comment]
        )->shouldBeCalled();

        $command = new BatchValidate([1, 2, 3], $admin, $comment);

        $handler = new BatchValidateHandler(
            $sheetRepository->reveal(),
            $batchJobQueue->reveal()
        );
        $result  = $handler->handle($command);

        $this->assertEquals(2, $result->count);
    }
}
