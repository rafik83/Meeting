<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\Happening\Webinar;

use DateTime;
use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\Happening\Webinar\RecordJobQueueInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record\ReconciliationCommand;

class RecordJobQueueAdapter extends AbstractJobQueueAdapter implements RecordJobQueueInterface
{
    public function removeReconciliation(int $happeningId): void
    {
        // TODO: find a way to cancel a job
        $this->removeJob(
            ReconciliationCommand::NAME,
            ['happening' => $happeningId]
        );
    }

    public function prepareReconciliation(int $happeningId, DateTimeInterface $reconciliationDate): void
    {
        $command = ReconciliationCommand::NAME;
        $args = ['happening' => $happeningId];

        $this->removeJob($command, $args);

        $job = new Job($command, $args);
        $date = new DateTime();
        $date->setTimestamp($reconciliationDate->getTimestamp());
        $job->setExecuteAfter($date);

        $this->setJob($job);
    }
}
