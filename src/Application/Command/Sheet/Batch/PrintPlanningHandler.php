<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Batch;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class PrintPlanningHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        JobQueueInterface $jobQueue,
        ExtraDataRepositoryInterface $extraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->jobQueue = $jobQueue;
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(PrintPlanning $command): BatchResult
    {
        $eventExtraDataSheetIds = new ExtraData(
            $command->event,
            Type::ADMIN_SHEET_BATCH_IDS,
            implode(',', $command->ids),
            $this->dateTime
        );

        $this->extraDataRepository->add($eventExtraDataSheetIds);
        $this->jobQueue->printPlanning(
            $eventExtraDataSheetIds,
            $command->orderBy,
            $command->admin->getEmail(),
            $command->admin->getLocale(),
            $command->printOption
        );

        return new BatchResult(
            $command->ids,
            sprintf(
                '%s%s.success',
                $command->getMessage(),
                $command->printOption
            )
        );
    }
}
