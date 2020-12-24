<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class BatchExportFormTemplateHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        JobQueueInterface $jobQueue,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->jobQueue = $jobQueue;
        $this->dateTime = $dateTime;
    }

    public function handle(BatchExportFormTemplate $command): BatchNoResult
    {
        $extraData = new ExtraData(
            $command->event,
            Type::ADMIN_USER_BATCH_IDS,
            implode(',', $command->ids),
            $this->dateTime
        );

        $this->extraDataRepository->add($extraData);

        $this->jobQueue->exportFormTemplateDataByUsers(
            $command->event,
            $command->formTemplate,
            $command->admin,
            $command->locale,
            $extraData
        );

        return new BatchNoResult();
    }
}
