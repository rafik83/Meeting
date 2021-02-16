<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Availability;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Group\Sheet\SheetCreatedByManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\User\Availability\ConfirmedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Availability\ConfirmationCalculator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AvailabilityConfirmationEventSubscriber implements EventSubscriberInterface
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ConfirmationCalculator */
    private $calculator;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param ConfirmationCalculator   $calculator
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, ConfirmationCalculator $calculator)
    {
        $this->sheetRepository = $sheetRepository;
        $this->calculator = $calculator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::USER_AVAILABILITY_CONFIRMED   => 'onUserAvailabilityConfirmed',
            Events::SHEET_CREATE_BY_GROUP_MANAGER => 'onSheetCreatedByGroupManager',
            Events::PARTICIPANT_ADDED             => 'onParticipantAdded',
            Events::PARTICIPANT_REMOVED           => 'onParticipantRemoved',
        ];
    }

    /**
     * @param ParticipantAddedEvent $event
     */
    public function onParticipantAdded(ParticipantAddedEvent $event): void
    {
        $sheet = $event->participant->getSheet();
        $sheet->setAvailabilityConfirmationStatus($this->calculator->getConfirmationStatusForSheet($sheet));

        $this->sheetRepository->set($sheet);
    }

    /**
     * @param ParticipantRemovedEvent $event
     */
    public function onParticipantRemoved(ParticipantRemovedEvent $event): void
    {
        $sheet = $event->sheet;
        $sheet->setAvailabilityConfirmationStatus($this->calculator->getConfirmationStatusForSheet($sheet));

        $this->sheetRepository->set($sheet);
    }

    /**
     * @param SheetCreatedByManagerEvent $sheetCreatedByManagerEvent
     */
    public function onSheetCreatedByGroupManager(SheetCreatedByManagerEvent $sheetCreatedByManagerEvent): void
    {
        $sheetCreatedByManagerEvent->sheet->setAvailabilityConfirmationStatus(
            $this->calculator->getConfirmationStatusForSheet($sheetCreatedByManagerEvent->sheet)
        );

        $this->sheetRepository->set($sheetCreatedByManagerEvent->sheet);
    }

    /**
     * @param ConfirmedEvent $confirmedEvent
     */
    public function onUserAvailabilityConfirmed(ConfirmedEvent $confirmedEvent): void
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent(
            $confirmedEvent->getUser(),
            $confirmedEvent->getEvent()
        );

        foreach ($sheets as $sheet) {
            $sheet->setAvailabilityConfirmationStatus($this->calculator->getConfirmationStatusForSheet($sheet));
            $this->sheetRepository->set($sheet);
        }
    }
}
