<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;

class JobQueueAdapter implements JobQueueInterface
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
     * @param Campaign $campaign
     */
    public function sendCampaign(Campaign $campaign)
    {
        $job = new Job('vimeet:campaign:send', [$campaign->getId()]);
        $job->addRelatedEntity($campaign);
        $this->entityManager->persist($job);
        $this->entityManager->flush($job);
    }
}
