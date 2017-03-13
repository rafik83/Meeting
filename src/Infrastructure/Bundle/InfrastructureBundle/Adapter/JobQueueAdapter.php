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
use Proximum\Vimeet\Domain\Model\Type;

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
     * {@inheritdoc}
     */
    public function sendCampaign(Campaign $campaign)
    {
        $job = new Job('vimeet:campaign:send', [$campaign->getId()]);
        $job->addRelatedEntity($campaign);
        $this->entityManager->persist($job);
        $this->entityManager->flush($job);
    }

    /**
     * {@inheritdoc}
     */
    public function printPlanning(array $types, $orderBy, $emailToNotify, $locale)
    {
        $typeOption = implode('', array_map(function (Type $type) {
            return sprintf('--types=%s', $type->getId());
        }, $types));

        $job = new Job('vimeet:planning:generate', [
            $typeOption,
            sprintf('--orderBy=%s', $orderBy),
            sprintf('--emailToNotify=%s', $emailToNotify),
            sprintf('--locale=%s', $locale),
        ]);
        $this->entityManager->persist($job);
        $this->entityManager->flush($job);
    }
}
