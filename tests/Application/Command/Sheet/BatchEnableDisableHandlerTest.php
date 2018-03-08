<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisable;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchEnableDisableHandlerTest extends TestCase
{
    public function testDisableHandle()
    {
        $admin = $this->prophesize(Admin::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);

        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);

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

        $meetingRepository->countMeetingsOfSheetByIds([1, 2, 3])->shouldBeCalled()->willReturn([
            1 => 0,
            2 => 0,
            3 => 0,
        ]);

        $sheetRepository->updateEnableStateBySheetsId([1, 2, 3], false)->shouldBeCalled();

        $batchJobQueue->createJob([1, 2, 3], $admin, ['state' => 'disable'])->shouldBeCalled();

        // Command
        $command = new BatchEnableDisable([1, 2, 3], false, $admin->reveal());
        $handler = new BatchEnableDisableHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
    }

    public function testDisableWithIgnoredSheets()
    {
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $admin = $this->prophesize(Admin::class);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $sheet3 = $this->prophesize(Sheet::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $batchJobQueue = $this->prophesize(BatchJobQueueInterface::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([
            1 => $sheet1->reveal(),
            2 => $sheet2->reveal(),
            3 => $sheet3->reveal(),
        ]);

        $meetingRepository
            ->countMeetingsOfSheetByIds([1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([
                1 => 0, // Sheet 1 has not meeting
                2 => 4, // Sheet 2 has meetings
                3 => 1, // Sheet 3 has meetings
            ]);

        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal(), 'fr')->shouldNotBeCalled();
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), 'fr')->shouldBeCalled()->willReturn('SheetTitle 2');
        $sheetInfoGuesser->guessSheetTitle($sheet3->reveal(), 'fr')->shouldBeCalled()->willReturn('SheetTitle 3');

        $sheetRepository->updateEnableStateBySheetsId([1], false)->shouldBeCalled();

        $batchJobQueue->createJob([1], $admin, ['state' => 'disable'])->shouldBeCalled();

        // Command
        $command = new BatchEnableDisable([1, 2, 3], false, $admin->reveal());
        $handler = new BatchEnableDisableHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);

        $this->assertEquals(
            new BatchResult(
                [$sheet1->reveal()],
                'flash.admin.sheet_batch.disable.warning',
                'SheetTitle 2, SheetTitle 3'
            ),
            $result
        );
    }

    public function testEnableHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $admin = $this->prophesize(Admin::class);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet1->isRefused()->shouldBeCalled()->willReturn(false);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->isRefused()->shouldBeCalled()->willReturn(true);
        $sheet2->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);
        $sheet3->isRefused()->shouldBeCalled()->willReturn(false);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn([
            1 => $sheet1->reveal(),
            2 => $sheet2->reveal(),
            3 => $sheet3->reveal(),
        ]);

        $sheetRepository->updateEnableStateBySheetsId([1, 3], true)->shouldBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countMeetingsOfSheetByIds([1, 2, 3])->shouldNotBeCalled();

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal(), 'fr')->shouldNotBeCalled();
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), 'fr')->shouldBeCalled()->willReturn('SheetTitle 2');
        $sheetInfoGuesser->guessSheetTitle($sheet3->reveal(), 'fr')->shouldNotBeCalled();

        $batchJobQueue = $this->prophesize(BatchJobQueueInterface::class);
        $batchJobQueue->createJob([1, 3], $admin, ['state' => 'enable'])->shouldBeCalled();

        $command = new BatchEnableDisable([1, 2, 3], true, $admin->reveal());
        $handler = new BatchEnableDisableHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);

        $this->assertEquals(
            new BatchResult(
                [$sheet1->reveal(), $sheet3->reveal()],
                'flash.admin.sheet_batch.enable.warning',
                'SheetTitle 2'
            ),
            $result
        );
    }
}
