<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\BatchRefuseJobQueue;

class BatchRefuseHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var BatchEnableDisableHandler */
    private $batchEnableDisableHandler;

    /** @var BatchRefuseJobQueue */
    private $batchRefuseJobQueue;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param SheetRepositoryInterface  $sheetRepository
     * @param BatchEnableDisableHandler $batchEnableDisableHandler
     * @param BatchRefuseJobQueue       $batchRefuseJobQueue
     * @param JobQueueInterface         $jobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchEnableDisableHandler $batchEnableDisableHandler,
        BatchRefuseJobQueue $batchRefuseJobQueue,
        JobQueueInterface $jobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchRefuseJobQueue = $batchRefuseJobQueue;
        $this->batchEnableDisableHandler = $batchEnableDisableHandler;
        $this->jobQueue = $jobQueue;
    }

    public function handle(BatchRefuse $batchRefuse)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchRefuse->ids);

        $firstSheet = reset($sheets);

        if (!$firstSheet instanceof Sheet) {
            return new BatchResult(
                [],
                $batchRefuse->getMessage() . 'refuse.success'
            );
        }

        $event = $firstSheet->getEvent();

        $batchDisable = new BatchEnableDisable($batchRefuse->ids, false, $batchRefuse->admin);
        $batchDisableResult = $this->batchEnableDisableHandler->handle($batchDisable);

        $disabledSheetsId = array_map(
            function (Sheet $sheet) {
                return $sheet->getId();
            },
            $batchDisableResult->sheets
        );

        if (!empty($disabledSheetsId)) {
            $this->sheetRepository->refuseBySheetsId($disabledSheetsId);
            $this->batchRefuseJobQueue->createJob($disabledSheetsId, $batchRefuse->admin);
            $this->jobQueue->sendEmailing($event, $disabledSheetsId, Events::SHEET_REFUSED);
        }

        if (!empty($batchDisableResult->ignoredSheetsMessage)) {
            return new BatchResult(
                $batchDisableResult->sheets,
                $batchRefuse->getMessage() . 'refuse.warning',
                $batchDisableResult->ignoredSheetsMessage
            );
        }

        return new BatchResult(
            $sheets,
            $batchRefuse->getMessage() . 'refuse.success'
        );
    }
}
