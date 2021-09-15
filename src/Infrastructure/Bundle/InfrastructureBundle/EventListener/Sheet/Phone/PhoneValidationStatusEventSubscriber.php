<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Phone;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Application\Event\Tip\AssignedEvent;
use Proximum\Vimeet\Application\Event\Tip\Event\CreatedEvent;
use Proximum\Vimeet\Application\Event\Tip\Event\UpdatedEvent;
use Proximum\Vimeet\Application\Event\Tip\RemovedEvent;
use Proximum\Vimeet\Application\Event\User\Phone\PhoneValidatedEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationCalculator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PhoneValidationStatusEventSubscriber implements EventSubscriberInterface
{
    /** @var ValidationCalculator */
    private $validationCalculator;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param ValidationCalculator     $validationCalculator
     * @param SheetRepositoryInterface $sheetRepository
     * @param JobQueueInterface        $jobQueue
     */
    public function __construct(
        ValidationCalculator $validationCalculator,
        SheetRepositoryInterface $sheetRepository,
        JobQueueInterface $jobQueue
    ) {
        $this->validationCalculator = $validationCalculator;
        $this->sheetRepository = $sheetRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * {@inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_CREATED        => 'onMeetingCreated',
            Events::MEETING_MOVED          => 'onMeetingMoved',
            Events::MEETING_REMOVED        => 'onMeetingChanged',
            Events::USER_PHONE_VALIDATED   => 'onUserPhoneValidated',
            Events::TIP_ASSIGNED           => 'onTipAssigned',
            Events::TIP_REMOVED_FROM_EVENT => 'onTipRemoved',
            Events::TIP_EVENT_UPDATED      => 'onTipEventUpdated',
            Events::TIP_EVENT_CREATED      => 'onTipEventCreated',
        ];
    }

    /**
     * @param MeetingRemovedEvent $event
     */
    public function onMeetingChanged(MeetingRemovedEvent $event): void
    {
        foreach ($event->getSheets() as $sheet) {
            $status = $this->validationCalculator->getValidationStatusForSheet($sheet);

            $sheet->setPhoneValidationStatus($status);
            $this->sheetRepository->set($sheet);
        }
    }

    /**
     * @param MeetingCreatedEvent $event
     */
    public function onMeetingCreated(MeetingCreatedEvent $event): void
    {
        foreach ($event->getMeeting()->getSheets() as $sheet) {
            $this->setStatusForSheet($sheet);
        }
    }

    /**
     * @param MeetingMovedEvent $event
     */
    public function onMeetingMoved(MeetingMovedEvent $event): void
    {
        foreach ($event->getSheets() as $sheet) {
            $this->setStatusForSheet($sheet);
        }
    }

    /**
     * @param AssignedEvent $event
     */
    public function onTipAssigned(AssignedEvent $event): void
    {
        $this->jobQueue->aggregatePhoneValidationStatus($event->getEvent());
    }

    /**
     * @param CreatedEvent $event
     */
    public function onTipEventCreated(CreatedEvent $event): void
    {
        $this->jobQueue->aggregatePhoneValidationStatus($event->getEvent());
    }

    /**
     * @param UpdatedEvent $event
     */
    public function onTipEventUpdated(UpdatedEvent $event): void
    {
        $this->jobQueue->aggregatePhoneValidationStatus($event->getEvent());
    }

    /**
     * @param RemovedEvent $event
     */
    public function onTipRemoved(RemovedEvent $event): void
    {
        $this->jobQueue->aggregatePhoneValidationStatus($event->getEvent());
    }

    /**
     * @param PhoneValidatedEvent $event
     */
    public function onUserPhoneValidated(PhoneValidatedEvent $event): void
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($event->getUser(), $event->getEvent());

        foreach ($sheets as $sheet) {
            $this->setStatusForSheet($sheet);
        }
    }

    /**
     * @param Sheet $sheet
     */
    private function setStatusForSheet(Sheet $sheet): void
    {
        $status = $this->validationCalculator->getValidationStatusForSheet($sheet);

        $sheet->setPhoneValidationStatus($status);
        $this->sheetRepository->set($sheet);
    }
}
