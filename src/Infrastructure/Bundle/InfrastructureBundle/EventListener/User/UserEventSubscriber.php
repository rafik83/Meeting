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
use Proximum\Vimeet\Application\Event\User\UserAssignedAsOwnerEvent;
use Proximum\Vimeet\Application\Event\User\UserRemovedAsOwnerEvent;
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
            Events::USER_ASSIGNED_AS_OWNER_OF_SHEET => 'onUserAssignedAsOwnerOfSheet',
            Events::USER_REMOVED_AS_OWNER_OF_SHEET => 'onUserRemovedAsOwnerOfSheet',
        ];
    }

    public function onUserAssignedAsOwnerOfSheet(UserAssignedAsOwnerEvent $event): void
    {
        $this->commandBus->handle(new Update($event->user, $event->sheet->getEvent()));
    }

    public function onUserRemovedAsOwnerOfSheet(UserRemovedAsOwnerEvent $event): void
    {
        $this->commandBus->handle(new Update($event->user, $event->sheet->getEvent()));
    }
}
