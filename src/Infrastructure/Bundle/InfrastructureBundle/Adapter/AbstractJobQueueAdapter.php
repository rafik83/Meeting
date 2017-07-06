<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;

abstract class AbstractJobQueueAdapter
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * JobQueueAdapter constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Job $job
     */
    protected function setJob(Job $job)
    {
        $this->entityManager->persist($job);
        $this->entityManager->flush($job);
    }

    /**
     * @param Job $job
     */
    protected function updateJob(Job $job)
    {
        $this->entityManager->flush($job);
    }
}
