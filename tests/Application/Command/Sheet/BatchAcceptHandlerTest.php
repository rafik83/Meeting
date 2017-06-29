<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\BatchAccept;
use Proximum\Vimeet\Application\Command\Sheet\BatchAcceptHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\BatchAcceptJobQueue;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchAcceptHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $date   = new \DateTime();
        $admin  = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);
        $type   = new Type($event);
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);
        $sheet3->markAsAccepted();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $batchJobQueue   = $this->prophesize(BatchAcceptJobQueue::class);

        $sheetRepository->getSheetsUnacceptedById([1, 2, 3])->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        $sheetRepository->updateStateBySheetsId(
            [1, 2, 3],
            Sheet::STATE_ACCEPTED
        )->shouldBeCalled();

        $batchJobQueue->createJob([1, 2, 3], $admin)->shouldBeCalled();

        $command = new BatchAccept([1, 2, 3], $admin);

        $handler = new BatchAcceptHandler(
            $sheetRepository->reveal(),
            $batchJobQueue->reveal()
        );
        $result  = $handler->handle($command);

        $this->assertEquals(3, $result->count);
    }
}
