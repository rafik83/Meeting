<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Command\Sheet\Export\PrepareExport;
use Proximum\Vimeet\Application\Command\Sheet\Export\PrepareExportHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class PrepareExportHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sheetSearchAdapter = $this->prophesize(SheetSearchAdapterInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $date = new \DateTime();

        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $sheetIdsView = new SheetIdsView([
            1,
            2,
            3,
            4
        ]);

        $sheetSearchAdapter
            ->getSheetIdsView($event->reveal(), [], 'fr', null)
            ->shouldBeCalled()
            ->willReturn($sheetIdsView)
        ;

        $extraData = new Event\ExtraData(
            $event->reveal(),
            Type::ADMIN_SHEET_EXPORT_IDS,
            '1,2,3,4',
            $date
        );
        $extraDataRepository->add($extraData)->shouldBeCalled();

        $jobQueue->exportSheet(
            $event->reveal(),
            $admin->reveal(),
            $extraData,
            'fr',
            true
        )->shouldBeCalled();

        $command = new PrepareExport(
            $event->reveal(),
            [],
            'fr',
            $admin->reveal(),
            true,
            null
        );

        $handler = new PrepareExportHandler(
            $sheetSearchAdapter->reveal(),
            $extraDataRepository->reveal(),
            $jobQueue->reveal(),
            $date
        );

        $handler->handle($command);
    }
}
