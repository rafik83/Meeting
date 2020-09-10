<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Export;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class PrepareExportHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var DateTimeInterface */
    private $dateTime;

    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        ExtraDataRepositoryInterface $extraDataRepository,
        JobQueueInterface $jobQueue,
        DateTimeInterface $dateTime
    ) {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
        $this->jobQueue = $jobQueue;
    }

    public function handle(PrepareExport $prepareExport): void
    {
        $sheetIdsView = $this->sheetSearchAdapter->getSheetIdsView(
            $prepareExport->event,
            $prepareExport->filters,
            $prepareExport->locale,
            $prepareExport->condition
        );

        $extraData = new ExtraData(
            $prepareExport->event,
            '',
            implode(',', $sheetIdsView->sheetIds),
            $this->dateTime
        );

        $this->extraDataRepository->add($extraData);

        $this->jobQueue->exportSheet(
            $prepareExport->event,
            $prepareExport->admin,
            $extraData,
            $prepareExport->locale,
            $prepareExport->displayNomenclatureIds
        );
    }
}
