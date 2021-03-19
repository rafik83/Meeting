<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Participant;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Participant\Add\OnParticipantAdded;
use Proximum\Vimeet\Application\Command\UserEventView\Update as UpdateUserEvent;
use Proximum\Vimeet\Application\Command\User\Event\PresenceDate\Persist;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Group\Sheet\SheetCreatedByManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantCreatedByGroupManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedFromApiEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedByGroupManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantUpdatedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantVisioTestedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantVisioToggledEvent;
use Proximum\Vimeet\Application\Event\User\RegistrationStepEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParticipantEventSubscriber implements EventSubscriberInterface
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    public function __construct(
        CommandBusInterface $commandBus,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->commandBus = $commandBus;
        $this->sheetIndexer = $sheetIndexer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::PARTICIPANT_UPDATED => 'onParticipantUpdated',
            Events::PARTICIPANT_ADDED => 'onParticipantAdded',
            Events::PARTICIPANT_IMPORTED => 'onParticipantImported',
            Events::PARTICIPANT_IMPORTED_FROM_API => 'onParticipantImportedFromApi',
            Events::PARTICIPANT_CREATED_BY_GROUP_MANAGER => 'onParticipantCreatedByGroupManager',
            Events::PARTICIPANT_REMOVED_BY_GROUP_MANAGER => 'onParticipantRemovedByGroupManager',
            Events::PARTICIPANT_REMOVED => 'onParticipantRemoved',
            Events::PARTICIPANT_VISIO_TOGGLED => 'onParticipantVisioToggled',
            Events::SHEET_CREATE_BY_GROUP_MANAGER => 'onSheetCreatedByGroupManager',
            Events::REGISTRATION_STEP => 'onRegistrationStepCompleted',
            Events::PARTICIPANT_VISIO_TESTED => 'onParticipantVisioTested',
        ];
    }

    public function onParticipantUpdated(ParticipantUpdatedEvent $participantUpdatedEvent): void
    {
        $this->commandBus->handle(
            new Persist(
                $participantUpdatedEvent->participant->getEvent(),
                $participantUpdatedEvent->participant->getUser(),
                $participantUpdatedEvent->templateData
            )
        );

        $this->commandBus->handle(
            new UpdateUserEvent(
                $participantUpdatedEvent->participant->getUser(),
                $participantUpdatedEvent->participant->getEvent()
            )
        );

        $this->sheetIndexer->updateSheets([$participantUpdatedEvent->participant->getSheet()]);
    }

    public function onParticipantAdded(ParticipantAddedEvent $participantAddedEvent): void
    {
        $this->commandBus->handle(
            new OnParticipantAdded($participantAddedEvent->participant, $participantAddedEvent->adderOfTheParticipant)
        );
    }

    public function onParticipantVisioToggled(ParticipantVisioToggledEvent $participantVisioToggledEvent): void
    {
        $this->commandBus->handle(
            new UpdateUserEvent(
                $participantVisioToggledEvent->participant->getUser(),
                $participantVisioToggledEvent->participant->getSheet()->getEvent()
            )
        );
    }

    public function onParticipantVisioTested(ParticipantVisioTestedEvent $participantVisioTestedEvent): void
    {
        $this->commandBus->handle(
            new UpdateUserEvent(
                $participantVisioTestedEvent->user,
                $participantVisioTestedEvent->event
            )
        );
    }

    public function onParticipantImported(ParticipantImportedEvent $participantImportedEvent): void
    {
        foreach ($participantImportedEvent->getSheets() as $sheet) {
            foreach ($sheet->getUsers() as $user) {
                $this->commandBus->handle(new UpdateUserEvent($user, $participantImportedEvent->getEvent()));
            }
        }
    }

    public function onParticipantImportedFromApi(
        ParticipantImportedFromApiEvent $participantImportedFromApiEvent
    ): void {
        $this->commandBus->handle(
            new UpdateUserEvent(
                $participantImportedFromApiEvent->participant->getUser(),
                $participantImportedFromApiEvent->participant->getEvent()
            )
        );
    }

    public function onParticipantCreatedByGroupManager(
        ParticipantCreatedByGroupManagerEvent $participantCreatedByGroupManagerEvent
    ): void {
        $this->commandBus->handle(
            new UpdateUserEvent(
                $participantCreatedByGroupManagerEvent->participant->getUser(),
                $participantCreatedByGroupManagerEvent->participant->getEvent()
            )
        );
    }

    public function onParticipantRemovedByGroupManager(
        ParticipantRemovedByGroupManagerEvent $participantRemovedByGroupManagerEvent
    ): void {
        $this->commandBus->handle(
            new UpdateUserEvent(
                $participantRemovedByGroupManagerEvent->user,
                $participantRemovedByGroupManagerEvent->sheet->getEvent()
            )
        );
    }

    public function onSheetCreatedByGroupManager(SheetCreatedByManagerEvent $sheetCreatedByManagerEvent): void
    {
        $event = $sheetCreatedByManagerEvent->sheet->getEvent();

        foreach ($sheetCreatedByManagerEvent->sheet->getUsers() as $user) {
            $this->commandBus->handle(new UpdateUserEvent($user, $event));
        }
    }

    public function onRegistrationStepCompleted(RegistrationStepEvent $registrationStepEvent): void
    {
        if (1 !== $registrationStepEvent->getStep()) {
            return;
        }

        $this->commandBus->handle(
            new UpdateUserEvent(
                $registrationStepEvent->getParticipant()->getUser(),
                $registrationStepEvent->getParticipant()->getEvent()
            )
        );
    }

    public function onParticipantRemoved(ParticipantRemovedEvent $participantRemovedEvent): void
    {
        $event = $participantRemovedEvent->sheet->getEvent();

        foreach ($participantRemovedEvent->usersRemovedFromSheet as $user) {
            $this->commandBus->handle(new UpdateUserEvent($user, $event));
        }
    }
}
