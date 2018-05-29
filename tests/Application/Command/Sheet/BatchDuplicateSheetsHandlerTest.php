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
use Proximum\Vimeet\Application\Command\Sheet\BatchDuplicateSheets;
use Proximum\Vimeet\Application\Command\Sheet\BatchDuplicateSheetsHandler;
use Proximum\Vimeet\Domain\Event\ExtraData\Type as ExtraDataType;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchDuplicateSheetsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $jobQueue = $this->prophesize(BatchJobQueueInterface::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $admin = $this->prophesize(Admin::class);
        $event = EventFactory::createEvent();
        $type = $this->prophesize(Type::class);
        $type->getId()->willReturn(1);
        $type->getEvent()->willReturn($event);
        $date = new \DateTime();

        $sheetRepository->getSheetsById([1, 2, 3, 4])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()]);

        $extraDataRepository
            ->getExtraDataForEvent($event, ExtraDataType::DUPLICATE_SHEET_IDS)
            ->shouldBeCalled()
            ->willReturn(null);

        $extraData = new ExtraData(
            $event,
            ExtraDataType::DUPLICATE_SHEET_IDS,
            '1, 2, 3, 4',
            $date
        );

        $extraDataRepository->add($extraData)
            ->shouldBeCalled();

        $jobQueue->createJob([1, 2, 3, 4], $admin->reveal(), ['typeId' => 1])
            ->shouldBeCalled();

        $handler = new BatchDuplicateSheetsHandler(
            $sheetRepository->reveal(),
            $extraDataRepository->reveal(),
            $jobQueue->reveal(),
            $date
        );
        $handler->handle(new BatchDuplicateSheets($admin->reveal(), $type->reveal(), [1, 2, 3, 4]));
    }
}
