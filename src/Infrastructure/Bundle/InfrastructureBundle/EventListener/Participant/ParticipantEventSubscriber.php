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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantUpdatedEvent;
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
}
