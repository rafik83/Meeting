<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Event;

use JMS\JobQueueBundle\Entity\Job;
use JMS\JobQueueBundle\Entity\Repository\JobRepository;
use Proximum\Vimeet\Application\Event\Event\KeyDatesUpdatedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Repository\Adapter\JobRepositoryAdapterInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\JobQueueAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventUpdatedEventSubscriber implements EventSubscriberInterface
{
    /** @var JobRepositoryAdapterInterface */
    private $jobRepository;

    /** @var JobQueueAdapter */
    private $jobQueueAdapter;

    /**
     * @param JobRepositoryAdapterInterface   $jobRepository
     * @param JobQueueAdapter $jobQueueAdapter
     */
    public function __construct(JobRepositoryAdapterInterface $jobRepository, JobQueueAdapter $jobQueueAdapter)
    {
        $this->jobRepository = $jobRepository;
        $this->jobQueueAdapter = $jobQueueAdapter;
    }

    /**
     * @param KeyDatesUpdatedEvent $keyDatesUpdatedEvent
     */
    public function onKeyDateUpdate(KeyDatesUpdatedEvent $keyDatesUpdatedEvent)
    {
        $job = $this->jobRepository->findGenerateVersionJobForEvent($keyDatesUpdatedEvent->getEvent());
        $smsActivationDate = $keyDatesUpdatedEvent->getEvent()->getConfiguration()->getSmsActivationDate();

        if (null !== $smsActivationDate) {
            if (null !== $job) {
                if (!$job->isFinished() && !$job->isRunning()) {
                    $this->jobQueueAdapter->scheduleVersionGeneration(
                        $keyDatesUpdatedEvent->getEvent(),
                        $smsActivationDate,
                        $job
                    );
                }
            } else {
                $this->jobQueueAdapter->scheduleVersionGeneration(
                    $keyDatesUpdatedEvent->getEvent(),
                    $smsActivationDate
                );
            }

            return;
        }

        if ($job !== null && null === $smsActivationDate) {
            if (!$job->isFinished() && !$job->isRunning()) {
                $job->setState(Job::STATE_CANCELED);
                $this->jobRepository->updateJob($job);
            }

            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_KEY_DATES_UPDATED => 'onKeyDateUpdate'
        ];
    }
}
