<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Participant;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Participant\Add\OnParticipantAdded;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Group\Sheet\SheetCreatedByManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantCreatedByGroupManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedFromApiEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedByGroupManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantUpdatedEvent;
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
            Events::SHEET_CREATE_BY_GROUP_MANAGER => 'onSheetCreatedByGroupManager',
            Events::REGISTRATION_STEP => 'onRegistrationStepCompleted',
        ];
    }

    public function onParticipantUpdated(ParticipantUpdatedEvent $participantUpdatedEvent): void
    {
        $this->sheetIndexer->updateSheets([$participantUpdatedEvent->participant->getSheet()]);
    }

    public function onParticipantAdded(ParticipantAddedEvent $participantAddedEvent): void
    {
        $this->commandBus->handle(
            new OnParticipantAdded($participantAddedEvent->participant, $participantAddedEvent->adderOfTheParticipant)
        );
    }

    public function onParticipantImported(ParticipantImportedEvent $participantImportedEvent): void
    {
        foreach ($participantImportedEvent->getSheets() as $sheet) {
            foreach ($sheet->getUsers() as $user) {
                $this->commandBus->handle(new Update($user, $participantImportedEvent->getEvent()));
            }
        }
    }

    public function onParticipantImportedFromApi(ParticipantImportedFromApiEvent $participantImportedFromApiEvent): void
    {
        $this->commandBus->handle(
            new Update(
                $participantImportedFromApiEvent->participant->getUser(),
                $participantImportedFromApiEvent->participant->getEvent()
            )
        );
    }

    public function onParticipantCreatedByGroupManager(
        ParticipantCreatedByGroupManagerEvent $participantCreatedByGroupManagerEvent
    ) {
        $this->commandBus->handle(
            new Update(
                $participantCreatedByGroupManagerEvent->participant->getUser(),
                $participantCreatedByGroupManagerEvent->participant->getEvent()
            )
        );
    }

    public function onParticipantRemovedByGroupManager(
        ParticipantRemovedByGroupManagerEvent $participantRemovedByGroupManagerEvent
    ) {
        $this->commandBus->handle(
            new Update(
                $participantRemovedByGroupManagerEvent->user,
                $participantRemovedByGroupManagerEvent->sheet->getEvent()
            )
        );
    }

    public function onSheetCreatedByGroupManager(SheetCreatedByManagerEvent $sheetCreatedByManagerEvent)
    {
        $event = $sheetCreatedByManagerEvent->sheet->getEvent();

        foreach ($sheetCreatedByManagerEvent->sheet->getUsers() as $user) {
            $this->commandBus->handle(new Update($user, $event));
        }
    }

    public function onRegistrationStepCompleted(RegistrationStepEvent $registrationStepEvent)
    {
        if (1 !== $registrationStepEvent->getStep()) {
            return;
        }

        $this->commandBus->handle(
            new Update(
                $registrationStepEvent->getParticipant()->getUser(),
                $registrationStepEvent->getParticipant()->getEvent()
            )
        );
    }

    public function onParticipantRemoved(ParticipantRemovedEvent $participantRemovedEvent)
    {
        $event = $participantRemovedEvent->sheet->getEvent();

        foreach ($participantRemovedEvent->usersRemovedFromSheet as $user) {
            $this->commandBus->handle(new Update($user, $event));
        }
    }
}
