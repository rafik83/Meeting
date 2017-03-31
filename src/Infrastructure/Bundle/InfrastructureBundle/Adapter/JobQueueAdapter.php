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
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\GenerateInvoiceCommand;

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
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function printPlanning(array $types, $orderBy, $emailToNotify, $locale)
    {
        $typeOptions = array_map(function (Type $type) {
            return sprintf('--types=%s', $type->getId());
        }, $types);

        $job = new Job(
            'vimeet:planning:generate',
            array_merge(
                $typeOptions,
                [
                    sprintf('--orderBy=%s', $orderBy),
                    sprintf('--emailToNotify=%s', $emailToNotify),
                    sprintf('--locale=%s', $locale),
                ]
            )
        );

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function generateInvoice(array $sheetIds, Admin $admin)
    {
        $job = new Job(GenerateInvoiceCommand::NAME, [
            'adminId'  => $admin->getId(),
            'sheetIds' => implode(',', $sheetIds),
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale)
    {
        $job = new Job('vimeet:order:export', [$event->getId(), $admin->getEmail(), $locale]);

        $this->setJob($job);
    }

    /**
     * @param Job $job
     */
    private function setJob(Job $job)
    {
        $this->entityManager->persist($job);
        $this->entityManager->flush($job);
    }
}
