<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class PrepareZipRecordArchiveHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(
        JobQueueInterface $jobQueue
    ) {
        $this->jobQueue = $jobQueue;
    }

    public function handle(PrepareZipRecordArchive $command): void
    {
        $this->jobQueue->zipRecordArchive(
            $command->happening,
            $command->admin,
            $command->locale
        );
    }
}
