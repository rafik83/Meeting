<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Aggregate;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\AbstractHappeningEvent;
use Proximum\Vimeet\Application\Event\Mass\Assignment\AssignmentUpdatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\AbstractParticipateEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\AbstractParticipantAssignedEvent;
use Proximum\Vimeet\Application\Event\Unavailability\AbstractUnavailabilityEvent;
use Proximum\Vimeet\Application\Event\Unavailability\System\SystemUnavailabilityForUserGeneratedEvent;
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
     * @param AbstractUnavailabilityEvent $unavailabilityEvent
     */
    public function onUnavailabilityChanged(AbstractUnavailabilityEvent $unavailabilityEvent): void
    {
        $this->participantUnavailableAggregator->aggregateUnavailability(
            $unavailabilityEvent->user,
            $unavailabilityEvent->event
        );
    }

    public function onSystemUnavailabilityGenerated(
        SystemUnavailabilityForUserGeneratedEvent $systemUnavailabilityForUserGeneratedEvent
    ): void {
        $this->participantUnavailableAggregator->aggregateUnavailability(
            $systemUnavailabilityForUserGeneratedEvent->user,
            $systemUnavailabilityForUserGeneratedEvent->event
        );
    }

    /**
     * @param AbstractParticipantAssignedEvent $event
     */
    public function onRequestParticipationChanged(AbstractParticipantAssignedEvent $event): void
    {
        $this->participantAssignedAggregator->aggregateAssignation($event->participant);
    }

    /**
     * @param AbstractHappeningEvent $happeningEvent
     */
    public function onHappeningParticipationChanged(AbstractHappeningEvent $happeningEvent): void
    {
        $this->participantUnavailableAggregator->aggregateUnavailability(
            $happeningEvent->participant->getUser(),
            $happeningEvent->participant->getSheet()->getEvent()
        );
    }

    /**
     * @param AssignmentUpdatedEvent $assignmentUpdatedEvent
     */
    public function onMassAssignmentChanged(AssignmentUpdatedEvent $assignmentUpdatedEvent): void
    {
        $this->participantUnavailableAggregator->aggregateUnavailability(
            $assignmentUpdatedEvent->participant->getUser(),
            $assignmentUpdatedEvent->participant->getSheet()->getEvent()
        );
    }

    /**
     * @param AbstractParticipateEvent $event
     */
    public function onMeetingChanged(AbstractParticipateEvent $event): void
    {
        $this->participantAssignedAggregator->aggregateAssignation($event->participant);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::UNAVAILABILITY_ADDED                 => 'onUnavailabilityChanged',
            Events::UNAVAILABILITY_REMOVED               => 'onUnavailabilityChanged',
            Events::USER_SYSTEM_UNAVAILABILITY_GENERATED => 'onSystemUnavailabilityGenerated',
            Events::REQUEST_PARTICIPATE                  => 'onRequestParticipationChanged',
            Events::REQUEST_UN_PARTICIPATE               => 'onRequestParticipationChanged',
            Events::HAPPENING_PARTICIPATE                => 'onHappeningParticipationChanged',
            Events::HAPPENING_UN_PARTICIPATE             => 'onHappeningParticipationChanged',
            Events::MASS_ASSIGNMENT_UPDATED              => 'onMassAssignmentChanged',
            Events::MEETING_PARTICIPATE                  => 'onMeetingChanged',
            Events::MEETING_UN_PARTICIPATE               => 'onMeetingChanged',
        ];
    }
}
