<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\Happening\Webinar;

use DateTime;
use DateTimeInterface;
use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\Happening\Webinar\RecordJobQueueInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record\ReconciliationCommand;

class RecordJobQueueAdapter extends AbstractJobQueueAdapter implements RecordJobQueueInterface
{
    public function removeReconciliation(int $happeningId): void
    {
        $this->removeJob(
            ReconciliationCommand::NAME,
            [$happeningId]
        );
    }

    public function prepareReconciliation(int $happeningId, DateTimeInterface $reconciliationDate): void
    {
        $command = ReconciliationCommand::NAME;
        $args = [$happeningId];

        $this->removeJob($command, $args);

        $job = new Job($command, $args);
        $date = new DateTime();
        $date->setTimestamp($reconciliationDate->getTimestamp());
        $job->setExecuteAfter($date);

        $this->setJob($job);
    }
}
