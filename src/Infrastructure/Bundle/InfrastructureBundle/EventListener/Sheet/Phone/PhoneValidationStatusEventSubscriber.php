<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Phone;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
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

    /**
     * @param ValidationCalculator     $validationCalculator
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        ValidationCalculator $validationCalculator,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->validationCalculator = $validationCalculator;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * {@inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_CREATED      => 'onMeetingCreated',
            Events::MEETING_MOVED        => 'onMeetingMoved',
            Events::MEETING_REMOVED      => 'onMeetingChanged',
            Events::USER_PHONE_VALIDATED => 'onUserPhoneValidated',
        ];
    }

    /**
     * @param MeetingRemovedEvent $event
     */
    public function onMeetingChanged(MeetingRemovedEvent $event)
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
    public function onMeetingCreated(MeetingCreatedEvent $event)
    {
        foreach ($event->getMeeting()->getSheets() as $sheet) {
            $this->setStatusForSheet($sheet);
        }
    }

    /**
     * @param MeetingMovedEvent $event
     */
    public function onMeetingMoved(MeetingMovedEvent $event)
    {
        foreach ($event->getSheets() as $sheet) {
            $this->setStatusForSheet($sheet);
        }
    }

    /**
     * @param PhoneValidatedEvent $event
     */
    public function onUserPhoneValidated(PhoneValidatedEvent $event)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($event->getUser(), $event->getEvent());

        foreach ($sheets as $sheet) {
            $this->setStatusForSheet($sheet);
        }
    }

    /**
     * @param Sheet $sheet
     */
    private function setStatusForSheet(Sheet $sheet)
    {
        $status = $this->validationCalculator->getValidationStatusForSheet($sheet);

        $sheet->setPhoneValidationStatus($status);
        $this->sheetRepository->set($sheet);
    }
}
