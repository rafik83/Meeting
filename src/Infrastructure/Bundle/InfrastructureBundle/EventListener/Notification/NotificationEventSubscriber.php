<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Notification;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Notification\SheetCompletenessEvent;
use Proximum\Vimeet\Application\Event\Transaction\AbstractTransactionEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionCreatedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionRemovedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Notification\Notification as NotificationConstant;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * NotificationEventSubscriber constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param TransactionRepositoryInterface  $transactionRepository
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->transactionRepository  = $transactionRepository;
    }

    /**
     * @param SheetCompletenessEvent $event
     */
    public function onSheetCompleteness(SheetCompletenessEvent $event)
    {
        $this->notificationRepository->removeByType(
            $event->getSheet(), NotificationConstant::TYPE_SHEET_TRANSLATION_COMPLETENESS
        );

        foreach ($event->getNotificationCompleteness() as $completeState) {
            if ($completeState !== true) {
                $this->notificationRepository->add(new Notification(
                    $event->getSheet(),
                    NotificationConstant::TYPE_SHEET_TRANSLATION_COMPLETENESS
                ));
                break;
            }
        }
    }

    /**
     * @param TransactionCreatedEvent $event
     */
    public function onTransactionCreated(TransactionCreatedEvent $event)
    {
        $this->updateTransactionNotification($event);
    }

    /**
     * @param TransactionUpdatedEvent $event
     */
    public function onTransactionUpdated(TransactionUpdatedEvent $event)
    {
        $this->updateTransactionNotification($event);
    }

    /**
     * @param TransactionRemovedEvent $event
     */
    public function onTransactionRemoved(TransactionRemovedEvent $event)
    {
        $this->updateTransactionNotification($event);
    }

    /**
     * @param AbstractTransactionEvent $event
     */
    private function updateTransactionNotification(AbstractTransactionEvent $event)
    {
        $this->notificationRepository->removeByType(
            $event->getTransaction()->getSheet(), NotificationConstant::TYPE_TRANSACTION_PENDING
        );

        $pendingTransactions = $this->transactionRepository->findPending($event->getTransaction()->getSheet());

        if (count($pendingTransactions) > 0) {
            $this->notificationRepository->add(new Notification(
                $event->getTransaction()->getSheet(),
                NotificationConstant::TYPE_TRANSACTION_PENDING
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_COMPLETENESS  => 'onSheetCompleteness',
            Events::TRANSACTION_CREATED => 'onTransactionCreated',
            Events::TRANSACTION_UPDATED => 'onTransactionUpdated',
            Events::TRANSACTION_REMOVED => 'onTransactionRemoved',
        ];
    }

}
