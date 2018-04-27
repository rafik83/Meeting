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
     * @param JobRepositoryAdapterInterface $jobRepository
     * @param JobQueueAdapter               $jobQueueAdapter
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
                if ($this->isJobUpdatable($job)) {
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

        if (null === $job && null === $smsActivationDate) {
            return;
        }

        if ($this->isJobUpdatable($job)) {
            $job->setState(Job::STATE_CANCELED);
            $this->jobRepository->updateJob($job);
        }
    }

    /**
     * @param Job $job
     *
     * @return bool
     */
    private function isJobUpdatable(Job $job): bool
    {
        return !$job->isFinished() && !$job->isRunning();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_KEY_DATES_UPDATED => 'onKeyDateUpdate',
        ];
    }
}
