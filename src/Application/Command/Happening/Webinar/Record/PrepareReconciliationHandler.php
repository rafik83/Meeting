<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\Happening\Webinar\RecordJobQueueInterface;

class PrepareReconciliationHandler
{
    /** @var RecordJobQueueInterface */
    private $jobQueue;

    public function __construct(RecordJobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    public function handle(PrepareReconciliation $prepareReconciliation): void
    {
        $happening = $prepareReconciliation->happening;

        if (!$happening->isWebinarRecorded()) {
            $this->jobQueue->removeReconciliation($happening->getId());

            return;
        }

        $date = $prepareReconciliation->dueDate;

        if (!$date instanceof \DateTimeInterface) {
            $date = clone $happening->getEnd();
            $date->modify('+5 minutes');
        }

        $this->jobQueue->prepareReconciliation(
            $happening->getId(),
            $date
        );
    }
}
