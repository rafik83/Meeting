<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisable;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchEnableDisableHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);

        // Actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);

        // Mock
        $sheetRepository     = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository   = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser    = $this->prophesize(SheetInfoGuesser::class);
        $batchJobQueue       = $this->prophesize(BatchJobQueueInterface::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([
            1 => $sheet1->setEnable(false),
            2 => $sheet2->setEnable(false),
            3 => $sheet3->setEnable(false),
        ]);

        $meetingRepository->countMeetingsOfSheetByIds([1, 2, 3])->shouldBeCalled()->willReturn([
            1 => 0,
            2 => 0,
            3 => 0,
        ]);

        $sheetRepository->updateEnableStateBySheetsId([1, 2, 3], false)->shouldBeCalled();

        $batchJobQueue->createJob([1, 2, 3], $admin, ['state' => BatchEnableDisableHandler::STATE_DISABLE])->shouldBeCalled();

        // Command
        $command = new BatchEnableDisable([1, 2, 3], false, $admin);
        $handler = new BatchEnableDisableHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
    }

    public function testHandleWithIgnoredSheets()
    {
        $event = EventFactory::createEvent();
        $date  = new \DateTime();

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $date);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->willReturn(1);
        $sheet1->getEvent()->willReturn($event);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->willReturn(2);
        $sheet2->getEvent()->willReturn($event);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->willReturn(3);
        $sheet3->getEvent()->willReturn($event);

        // Mock
        $sheetRepository     = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository   = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser    = $this->prophesize(SheetInfoGuesser::class);
        $batchJobQueue       = $this->prophesize(BatchJobQueueInterface::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([
            1 => $sheet1->reveal(),
            2 => $sheet2->reveal(),
            3 => $sheet3->reveal(),
        ]);

        $meetingRepository
            ->countMeetingsOfSheetByIds([1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([
                1 => 2,
                2 => 4,
                3 => 1,
            ]);

        $sheetInfoGuesser->guessSheetTitle(Argument::type(Sheet::class), 'fr')
            ->shouldBeCalledTimes(3)->willReturn("SheetName");

        $sheetRepository->updateEnableStateBySheetsId([], false)->shouldNotBeCalled();

        $batchJobQueue->createJob([], $admin, ['state' => false])->shouldNotBeCalled();

        // Command
        $command = new BatchEnableDisable([1, 2, 3], false, $admin);
        $handler = new BatchEnableDisableHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
        $this->assertEquals("SheetName, SheetName, SheetName", $result->ignoredSheetsMessage);
    }
}
