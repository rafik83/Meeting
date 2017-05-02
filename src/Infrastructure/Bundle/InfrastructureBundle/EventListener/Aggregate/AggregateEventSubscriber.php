<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Aggregate;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\AbstractHappeningEvent;
use Proximum\Vimeet\Application\Event\Mass\Assignment\AssignmentUpdatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\AbstractParticipateEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\AbstractParticipantAssignedEvent;
use Proximum\Vimeet\Application\Event\Unavailability\AbstractUnavailabilityEvent;
use Proximum\Vimeet\Domain\Request\ParticipantAssignedAggregator;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AggregateEventSubscriber implements EventSubscriberInterface
{
    /** @var ParticipantUnavailableAggregator */
    private $participantUnavailableAggregator;

    /** @var ParticipantAssignedAggregator */
    private $participantAssignedAggregator;

    /**
     * @param ParticipantUnavailableAggregator $participantUnavailableAggregator
     * @param ParticipantAssignedAggregator    $participantAssignedAggregator
     */
    public function __construct(
        ParticipantUnavailableAggregator $participantUnavailableAggregator,
        ParticipantAssignedAggregator $participantAssignedAggregator
    ) {
        $this->participantUnavailableAggregator = $participantUnavailableAggregator;
        $this->participantAssignedAggregator = $participantAssignedAggregator;
    }

    /**
     * @param AbstractUnavailabilityEvent $event
     */
    public function onUnavailabilityChanged(AbstractUnavailabilityEvent $event)
    {
        $this->participantUnavailableAggregator->aggregateUnavailability($event->participant);
    }

    /**
     * @param AbstractParticipantAssignedEvent $event
     */
    public function onRequestParticipationChanged(AbstractParticipantAssignedEvent $event)
    {
        $this->participantAssignedAggregator->aggregateAssignation($event->participant);
    }

    /**
     * @param AbstractHappeningEvent $event
     */
    public function onHappeningParticipationChanged(AbstractHappeningEvent $event)
    {
        $this->participantUnavailableAggregator->aggregateUnavailability($event->participant);
    }

    /**
     * @param AssignmentUpdatedEvent $event
     */
    public function onMassAssignmentChanged(AssignmentUpdatedEvent $event)
    {
        $this->participantUnavailableAggregator->aggregateUnavailability($event->participant);
    }

    /**
     * @param AbstractParticipateEvent $event
     */
    public function onMeetingChanged(AbstractParticipateEvent $event)
    {
        $this->participantAssignedAggregator->aggregateAssignation($event->participant);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::UNAVAILABILITY_ADDED        => 'onUnavailabilityChanged',
            Events::UNAVAILABILITY_REMOVED      => 'onUnavailabilityChanged',
            Events::REQUEST_PARTICIPATE         => 'onRequestParticipationChanged',
            Events::REQUEST_UN_PARTICIPATE      => 'onRequestParticipationChanged',
            Events::HAPPENING_PARTICIPATE       => 'onHappeningParticipationChanged',
            Events::HAPPENING_UN_PARTICIPATE    => 'onHappeningParticipationChanged',
            Events::MASS_ASSIGNMENT_UPDATED     => 'onMassAssignmentChanged',
            Events::MEETING_PARTICIPATE         => 'onMeetingChanged',
            Events::MEETING_UN_PARTICIPATE      => 'onMeetingChanged',
        ];
    }
}
