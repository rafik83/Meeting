<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Adapter;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;
use JMS\JobQueueBundle\Entity\Repository\JobRepository;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Adapter\JobRepositoryAdapterInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\User\Agenda\Version\GenerateVersionsCommand;

class JobRepositoryAdapter implements JobRepositoryAdapterInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return JobRepository
     */
    public function getJobRepository(): JobRepository
    {
        return $this->entityManager->getRepository('JMSJobQueueBundle:Job');
    }

    /**
     * {@inheritdoc}
     */
    public function findGenerateVersionJobForEvent(Event $event): ?Job
    {
        /** @var Job[] $jobs */
        $jobs = $this->getJobRepository()->findAllForRelatedEntity($event);

        foreach ($jobs as $job) {
            if ($job->getCommand() === GenerateVersionsCommand::NAME && !$job->isCanceled()) {
                return $job;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateJob(Job $job)
    {
        $this->entityManager->flush($job);
    }
}
