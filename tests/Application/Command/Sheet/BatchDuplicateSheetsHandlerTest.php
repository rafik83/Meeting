<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchDuplicateSheets;
use Proximum\Vimeet\Application\Command\Sheet\BatchDuplicateSheetsHandler;
use Proximum\Vimeet\Domain\Event\ExtraData\Type as ExtraDataType;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
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
        $originalEvent = $this->prophesize(Event::class);
        $event = EventFactory::createEvent();
        $type = $this->prophesize(Type::class);
        $type->getId()->willReturn(1);
        $type->getEvent()->willReturn($event);
        $date = new \DateTime();

        $sheetRepository->getSheetsById([1, 2, 3, 4])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()]);

        $extraData = new ExtraData(
            $event,
            ExtraDataType::ADMIN_SHEET_BATCH_IDS,
            '1, 2, 3, 4',
            $date
        );

        $extraDataRepository->add($extraData)
            ->shouldBeCalled();

        $jobQueue->createJob(
            [1, 2, 3, 4],
            $admin->reveal(),
            [
                'typeId' => 1,
                'extraDataId' => null,
                'originalEventId' => null,
            ])
            ->shouldBeCalled();

        $handler = new BatchDuplicateSheetsHandler(
            $sheetRepository->reveal(),
            $extraDataRepository->reveal(),
            $jobQueue->reveal(),
            $date
        );
        $handler->handle(
            new BatchDuplicateSheets(
                $originalEvent->reveal(),
                $admin->reveal(),
                $type->reveal(),
                [1, 2, 3, 4]
            )
        );
    }
}
