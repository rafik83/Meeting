<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet;

use FOS\ElasticaBundle\Persister\ObjectPersister;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\CancelRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\CreateRequestEvent;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionCreatedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionRemovedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetPopulateEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ObjectPersister
     */
    private $persister;

    /**
     * @param ObjectPersister $persister
     */
    public function __construct(ObjectPersister $persister)
    {
        $this->persister = $persister;
    }

    /**
     * @param StepDoneEvent $event
     */
    public function onPackageStep(StepDoneEvent $event)
    {
        $this->updateSheetIndexation($event->getSheet());
    }

    /**
     * @param OrderConfirmEvent $event
     */
    public function onOrderConfirmed(OrderConfirmEvent $event)
    {
        $this->updateSheetIndexation($event->getOrder()->getSheet());
    }

    /**
     * @param TransactionCreatedEvent $event
     */
    public function onTransactionCreated(TransactionCreatedEvent $event)
    {
        $this->updateSheetIndexation($event->getTransaction()->getSheet());
    }

    /**
     * @param TransactionUpdatedEvent $event
     */
    public function onTransactionUpdated(TransactionUpdatedEvent $event)
    {
        $this->updateSheetIndexation($event->getTransaction()->getSheet());
    }

    /**
     * @param TransactionConfirmEvent $event
     */
    public function onTransactionConfirmed(TransactionConfirmEvent $event)
    {
        $this->updateSheetIndexation($event->getTransaction()->getSheet());
    }

    /**
     * @param TransactionRemovedEvent $event
     */
    public function onTransactionRemoved(TransactionRemovedEvent $event)
    {
        $this->updateSheetIndexation($event->getTransaction()->getSheet());
    }

    /**
     * @param ParticipateEvent $event
     */
    public function onHappeningParticipated(ParticipateEvent $event)
    {
        $this->updateSheetIndexation($event->getSheet());
    }

    /**
     * @param CreateRequestEvent $event
     */
    public function onMeetingRequestCreated(CreateRequestEvent $event)
    {
        // Update "from" and "to" sheets of the meeting request
        $this->updateSheetIndexation($event->getRequest()->getFromSheet());
        $this->updateSheetIndexation($event->getRequest()->getToSheet());
    }

    /**
     * @param CancelRequestEvent $event
     */
    public function onMeetingRequestCanceled(CancelRequestEvent $event)
    {
        // Update "from" and "to" sheets of the meeting request
        $this->updateSheetIndexation($event->getRequest()->getFromSheet());
        $this->updateSheetIndexation($event->getRequest()->getToSheet());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ORDER_CONFIRMED          => 'onOrderConfirmed',
            Events::PACKAGE_STEP_DONE        => 'onPackageStep',
            Events::TRANSACTION_CREATED      => 'onTransactionCreated',
            Events::TRANSACTION_UPDATED      => 'onTransactionUpdated',
            Events::TRANSACTION_CONFIRMED    => 'onTransactionConfirmed',
            Events::TRANSACTION_REMOVED      => 'onTransactionRemoved',
            Events::HAPPENING_PARTICIPATED   => 'onHappeningParticipated',
            Events::MEETING_REQUEST_CREATED  => 'onMeetingRequestCreated',
            Events::MEETING_REQUEST_CANCELED => 'onMeetingRequestCanceled',
        ];
    }

    /**
     * @param Sheet $sheet
     */
    private function updateSheetIndexation(Sheet $sheet)
    {
        $this->persister->replaceOne($sheet);
    }
}
