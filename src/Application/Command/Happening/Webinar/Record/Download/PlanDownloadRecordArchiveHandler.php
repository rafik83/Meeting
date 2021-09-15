<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Download;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class PlanDownloadRecordArchiveHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    public function handle(PlanDownloadRecordArchive $recordArchiveReconciliation): void
    {
        $this->jobQueue->planDownloadRecordArchive(
            $recordArchiveReconciliation->happening,
            $recordArchiveReconciliation->dueDate
        );
    }
}
