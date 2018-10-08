<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\User;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\OwnerChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::SHEET_OWNER_CHANGED => 'onSheetOwnerChanged',
        ];
    }

    public function onSheetOwnerChanged(OwnerChangedEvent $event): void
    {
        $this->commandBus->handle(new Update($event->previousOwner, $event->sheet->getEvent()));
        $this->commandBus->handle(new Update($event->sheet->getOwner(), $event->sheet->getEvent()));
    }
}
