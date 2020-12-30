<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Notification;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Notification\SheetCompletenessEvent;
use Proximum\Vimeet\Application\Event\Transaction\AbstractTransactionEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionCreatedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionRemovedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Notification\Notification as NotificationConstant;
use Proximum\Vimeet\Domain\Order\Balance;
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
     * @var Balance
     */
    private $balance;

    /**
     * NotificationEventSubscriber constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param TransactionRepositoryInterface  $transactionRepository
     * @param Balance                         $balance
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        TransactionRepositoryInterface $transactionRepository,
        Balance $balance
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->transactionRepository  = $transactionRepository;
        $this->balance                = $balance;
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
            if (true !== $completeState) {
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

        $balance             = $this->balance->getBalance($event->getTransaction()->getSheet());
        $pendingTransactions = $this->transactionRepository->findPending($event->getTransaction()->getSheet());

        // persist notification if transaction pending and balance is positive
        if (count($pendingTransactions) > 0 && $balance > 0) {
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
