<?php

namespace Proximum\Vimeet\Domain\Repository\Adapter;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Domain\Model\Event;

interface JobRepositoryAdapterInterface
{
    /**
     * @param Event $event
     *
     * @return Job|null
     */
    public function findGenerateVersionJobForEvent(Event $event): ?Job;

    /**
     * @param Job $job
     */
    public function updateJob(Job $job);
}
