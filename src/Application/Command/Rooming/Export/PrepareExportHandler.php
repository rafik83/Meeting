<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class PrepareExportHandler
{
    /** @var JobQueueInterface */
    private $jobQueueAdapter;

    public function __construct(JobQueueInterface $jobQueueAdapter)
    {
        $this->jobQueueAdapter = $jobQueueAdapter;
    }

    public function handle(PrepareExport $command): void
    {
        $this->jobQueueAdapter->exportRoomingList($command->event, $command->admin, $command->locale);
    }
}
