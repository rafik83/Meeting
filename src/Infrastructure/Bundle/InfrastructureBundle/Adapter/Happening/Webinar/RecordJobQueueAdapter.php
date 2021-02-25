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

    public function prepareReconciliation(int $happeningId, DateTimeInterface $reconciliationDate): void
    {
        $job = $this->createJob($happeningId);

        $date = new DateTime();
        $date->setTimestamp($reconciliationDate->getTimestamp());
        $job->setExecuteAfter($date);

        $this->setJob($job);
    }

    private function createJob($happeningId): Job
    {
        $command = ReconciliationCommand::NAME;
        $args = ['happening' => $happeningId];

        return new Job($command, $args);
    }
}
