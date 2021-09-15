<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Aggregate;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\HappeningParticipationAutomaticallyUpdatedEvent;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\Mass\Assignment\AssignmentUpdatedEvent;
use Proximum\Vimeet\Application\Event\Mass\Unavailability\DispatchedEvent;
use Proximum\Vimeet\Application\Event\Meeting\AbstractParticipateEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Slot\AbstractSlotEvent;
use Proximum\Vimeet\Application\Event\Unavailability\AbstractUnavailabilityEvent;
use Proximum\Vimeet\Application\Event\User\RegistrationEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculatorInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\JobQueueAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetAggregateEventSubscriber implements EventSubscriberInterface
{
    /** @var AvailableSlotCalculatorInterface */
    private $availableSlotCalculator;

    /** @var JobQueueAdapter */
    private $jobQueueAdapter;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface         $sheetRepository
     * @param AvailableSlotCalculatorInterface $availableSlotCalculator
     * @param JobQueueAdapter                  $jobQueueAdapter
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AvailableSlotCalculatorInterface $availableSlotCalculator,
        JobQueueAdapter $jobQueueAdapter
    ) {
        $this->availableSlotCalculator = $availableSlotCalculator;
        $this->jobQueueAdapter = $jobQueueAdapter;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param ParticipateEvent $participateEvent
     */
    public function onHappeningParticipation(ParticipateEvent $participateEvent): void
    {
        $sheets = $this->sheetRepository->getSheetsByUsersAndEvent(
            $participateEvent->getSheet()->getUsers(),
            $participateEvent->getSheet()->getEvent()
        );

        foreach ($sheets as $sheet) {
            $this->jobQueueAdapter->aggregateSheetAvailableSlot($sheet);
        }
    }

    public function onHappeningParticipationAutomaticallyUpadted(HappeningParticipationAutomaticallyUpdatedEvent $event): void
    {
        $sheets = $this->sheetRepository->getSheetsByUsersAndEvent(
            $event->sheet->getUsers(),
            $event->sheet->getEvent()
        );

        foreach ($sheets as $sheet) {
            $this->jobQueueAdapter->aggregateSheetAvailableSlot($sheet);
        }
    }

    /**
     * @param AssignmentUpdatedEvent $event
     */
    public function onMassAssignmentChanged(AssignmentUpdatedEvent $event): void
    {
        $this->availableSlotCalculator->calculateAvailableSlotForSheet($event->participant->getSheet());
    }

    /**
     * @param DispatchedEvent $dispatchedEvent
     */
    public function onMassUnavailabilityDispatched(DispatchedEvent $dispatchedEvent): void
    {
        $this->jobQueueAdapter->aggregateAvailableSlot($dispatchedEvent->event);
    }

    /**
     * @param AbstractParticipateEvent $event
     */
    public function onMeetingChanged(AbstractParticipateEvent $event): void
    {
        $this->availableSlotCalculator->calculateAvailableSlotForSheet($event->participant->getSheet());
    }

    /**
     * @param MeetingMovedEvent $meetingMovedEvent
     */
    public function onMeetingMoved(MeetingMovedEvent $meetingMovedEvent): void
    {
        foreach ($meetingMovedEvent->getSheets() as $sheet) {
            $this->availableSlotCalculator->calculateAvailableSlotForSheet($sheet);
        }
    }

    /**
     * @param ParticipantAddedEvent $participantAddedEvent
     */
    public function onParticipantAdded(ParticipantAddedEvent $participantAddedEvent): void
    {
        $this->availableSlotCalculator->calculateAvailableSlotForSheet($participantAddedEvent->participant->getSheet());
    }

    /**
     * @param ParticipantRemovedEvent $participantRemovedEvent
     */
    public function onParticipantRemoved(ParticipantRemovedEvent $participantRemovedEvent): void
    {
        $this->availableSlotCalculator->calculateAvailableSlotForSheet($participantRemovedEvent->sheet);
    }

    /**
     * @param AbstractSlotEvent $abstractSlotEvent
     */
    public function onSlotModification(AbstractSlotEvent $abstractSlotEvent): void
    {
        $this->jobQueueAdapter->aggregateAvailableSlot($abstractSlotEvent->event);
    }

    /**
     * @param AbstractUnavailabilityEvent $event
     */
    public function onUnavailabilityChanged(AbstractUnavailabilityEvent $event): void
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($event->user, $event->event);

        foreach ($sheets as $sheet) {
            $this->availableSlotCalculator->calculateAvailableSlotForSheet($sheet);
        }
    }

    /**
     * @param RegistrationEvent $registrationEvent
     */
    public function onUserRegistration(RegistrationEvent $registrationEvent): void
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($registrationEvent->user, $registrationEvent->event);

        foreach ($sheets as $sheet) {
            $this->availableSlotCalculator->calculateAvailableSlotForSheet($sheet);
        }
    }

    /**
     * {@inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => 'onHappeningParticipationAutomaticallyUpadted',
            Events::HAPPENING_PARTICIPATED => 'onHappeningParticipation',
            Events::MASS_UNAVAILABILITY_DISPATCHED => 'onMassUnavailabilityDispatched',
            Events::SLOT_GENERATED => 'onSlotModification',
            Events::SLOT_DELETED => 'onSlotModification',
            Events::SLOT_TOGGLE_LOCKED => 'onSlotModification',
            Events::UNAVAILABILITY_ADDED => 'onUnavailabilityChanged',
            Events::UNAVAILABILITY_REMOVED => 'onUnavailabilityChanged',
            Events::MASS_ASSIGNMENT_UPDATED => 'onMassAssignmentChanged',
            Events::MEETING_PARTICIPATE => 'onMeetingChanged',
            Events::MEETING_MOVED => 'onMeetingMoved',
            Events::MEETING_UN_PARTICIPATE => 'onMeetingChanged',
            Events::PARTICIPANT_ADDED => 'onParticipantAdded',
            Events::PARTICIPANT_REMOVED => 'onParticipantRemoved',
            Events::USER_REGISTRATION => 'onUserRegistration',
        ];
    }
}
