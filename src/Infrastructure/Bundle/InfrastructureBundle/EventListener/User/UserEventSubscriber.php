<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\User;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\OwnerChangedEvent;
use Proximum\Vimeet\Application\Event\User\ConnectedEvent;
use Proximum\Vimeet\Application\Event\User\Event\UpdatedEvent;
use Proximum\Vimeet\Application\Event\User\UserEmailChangeActivatedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    private CommandBusInterface $commandBus;
    private SheetRepositoryInterface $sheetRepository;
    private NotificationPublisherInterface $notificationPublisher;

    public function __construct(
        CommandBusInterface $commandBus,
        SheetRepositoryInterface $sheetRepository,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->commandBus = $commandBus;
        $this->sheetRepository = $sheetRepository;
        $this->notificationPublisher = $notificationPublisher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::SHEET_OWNER_CHANGED => 'onSheetOwnerChanged',
            Events::USER_EMAIL_CHANGE_ACTIVATED => 'onUserEmailChangeActivated',
            Events::USER_EVENT_UPDATED => 'onUserUpdated',
            Events::USER_CONNECTED => 'onUserConnected',
        ];
    }

    public function onSheetOwnerChanged(OwnerChangedEvent $event): void
    {
        $this->commandBus->handle(new Update($event->previousOwner, $event->sheet->getEvent()));
        $this->commandBus->handle(new Update($event->sheet->getOwner(), $event->sheet->getEvent()));
    }

    public function onUserEmailChangeActivated(UserEmailChangeActivatedEvent $event): void
    {
        $sheets = $this->sheetRepository->getByUser($event->user);

        foreach ($sheets as $sheet) {
            $this->commandBus->handle(
                new Update(
                    $event->user,
                    $sheet->getEvent()
                )
            );
        }
    }

    public function onUserUpdated(UpdatedEvent $updatedEvent): void
    {
        $this->commandBus->handle(
            new Update(
                $updatedEvent->user,
                $updatedEvent->event
            )
        );
    }

    public function onUserConnected(ConnectedEvent $event)
    {
        $this->notificationPublisher->publishUserConnectionNotification($event->getSheet(), $event->getUser());
    }
}
