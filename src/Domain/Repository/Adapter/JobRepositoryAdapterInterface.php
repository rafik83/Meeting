<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
