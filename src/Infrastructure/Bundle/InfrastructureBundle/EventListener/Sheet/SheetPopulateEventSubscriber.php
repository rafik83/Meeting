<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\HappeningParticipationAutomaticallyUpdatedEvent;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ApprovedRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\CancelRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\CreateRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\RefusedRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\UnapprovedRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\UnRefusedRequestEvent;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Order\OrderUpdatedEvent;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetInvoicedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionCreatedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionRemovedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetPopulateEventSubscriber implements EventSubscriberInterface
{
    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /**
     * @param SheetIndexerInterface $sheetIndexer
     */
    public function __construct(SheetIndexerInterface $sheetIndexer)
    {
        $this->sheetIndexer = $sheetIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ORDER_CONFIRMED            => 'onOrderConfirmed',
            Events::ORDER_UPDATED              => 'onOrderUpdated',
            Events::PACKAGE_STEP_DONE          => 'onPackageStep',
            Events::TRANSACTION_CREATED        => 'onTransactionCreated',
            Events::TRANSACTION_UPDATED        => 'onTransactionUpdated',
            Events::TRANSACTION_CONFIRMED      => 'onTransactionConfirmed',
            Events::TRANSACTION_REMOVED        => 'onTransactionRemoved',
            Events::HAPPENING_PARTICIPATED     => 'onHappeningParticipated',
            Events::HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => 'onHappeningParticipationAutomaticallyUpdated',
            Events::MEETING_REQUEST_CREATED    => 'onMeetingRequestCreated',
            Events::MEETING_REQUEST_CANCELED   => 'onMeetingRequestCanceled',
            Events::MEETING_REQUEST_REFUSED    => 'onMeetingRequestRefused',
            Events::MEETING_REQUEST_APPROVED   => 'onMeetingRequestApproved',
            Events::MEETING_REQUEST_UNAPPROVED => 'onMeetingRequestUnapproved',
            Events::MEETING_REQUEST_UNREFUSED  => 'onMeetingRequestUnrefused',
            Events::PARTICIPANT_IMPORTED       => 'onParticipantImported',
            Events::SHEET_INVOICED             => 'onSheetInvoiced',
            Events::MEETING_CREATED            => 'onMeetingCreated',
            Events::MEETING_REMOVED            => 'onMeetingRemoved',
        ];
    }

    /**
     * @param StepDoneEvent $event
     */
    public function onPackageStep(StepDoneEvent $event): void
    {
        $this->updateSheetIndexation([$event->getSheet()]);
    }

    /**
     * @param OrderConfirmEvent $event
     */
    public function onOrderConfirmed(OrderConfirmEvent $event): void
    {
        $this->updateSheetIndexation([$event->getOrder()->getSheet()]);
    }

    /**
     * @param OrderUpdatedEvent $event
     */
    public function onOrderUpdated(OrderUpdatedEvent $event): void
    {
        $this->updateSheetIndexation([$event->getOrder()->getSheet()]);
    }

    /**
     * @param TransactionCreatedEvent $event
     */
    public function onTransactionCreated(TransactionCreatedEvent $event): void
    {
        $this->updateSheetIndexation([$event->getTransaction()->getSheet()]);
    }

    /**
     * @param TransactionUpdatedEvent $event
     */
    public function onTransactionUpdated(TransactionUpdatedEvent $event): void
    {
        $this->updateSheetIndexation([$event->getTransaction()->getSheet()]);
    }

    /**
     * @param TransactionConfirmedEvent $event
     */
    public function onTransactionConfirmed(TransactionConfirmedEvent $event): void
    {
        $this->updateSheetIndexation([$event->getTransaction()->getSheet()]);
    }

    /**
     * @param TransactionRemovedEvent $event
     */
    public function onTransactionRemoved(TransactionRemovedEvent $event): void
    {
        $this->updateSheetIndexation([$event->getTransaction()->getSheet()]);
    }

    /**
     * @param ParticipateEvent $event
     */
    public function onHappeningParticipated(ParticipateEvent $event): void
    {
        $this->updateSheetIndexation([$event->getSheet()]);
    }

    public function onHappeningParticipationAutomaticallyUpdated(HappeningParticipationAutomaticallyUpdatedEvent $event): void
    {
        $this->updateSheetIndexation([$event->sheet]);
    }

    /**
     * @param MeetingRemovedEvent $event
     */
    public function onMeetingRemoved(MeetingRemovedEvent $event): void
    {
        $this->updateSheetIndexation($event->getSheets());
    }

    /**
     * @param CreateRequestEvent $event
     */
    public function onMeetingRequestCreated(CreateRequestEvent $event): void
    {
        $this->updateSheetsIndexationFromRequest($event->getRequest());
    }

    /**
     * @param CancelRequestEvent $event
     */
    public function onMeetingRequestCanceled(CancelRequestEvent $event): void
    {
        $this->updateSheetsIndexationFromRequest($event->getRequest());
    }

    /**
     * @param RefusedRequestEvent $event
     */
    public function onMeetingRequestRefused(RefusedRequestEvent $event): void
    {
        $this->updateSheetsIndexationFromRequest($event->getRequest());
    }

    /**
     * @param ApprovedRequestEvent $event
     */
    public function onMeetingRequestApproved(ApprovedRequestEvent $event): void
    {
        $this->updateSheetsIndexationFromRequest($event->getRequest());
    }

    /**
     * @param UnapprovedRequestEvent $event
     */
    public function onMeetingRequestUnapproved(UnapprovedRequestEvent $event): void
    {
        $this->updateSheetsIndexationFromRequest($event->getRequest());
    }

    /**
     * @param UnRefusedRequestEvent $event
     */
    public function onMeetingRequestUnrefused(UnRefusedRequestEvent $event): void
    {
        $this->updateSheetsIndexationFromRequest($event->getRequest());
    }

    /**
     * @param ParticipantImportedEvent $event
     */
    public function onParticipantImported(ParticipantImportedEvent $event): void
    {
        $this->updateSheetIndexation($event->getSheets());
    }

    /**
     * @param SheetInvoicedEvent $sheetInvoicedEvent
     */
    public function onSheetInvoiced(SheetInvoicedEvent $sheetInvoicedEvent): void
    {
        $this->updateSheetIndexation($sheetInvoicedEvent->getSheets());
    }

    /**
     * @param MeetingCreatedEvent $event
     */
    public function onMeetingCreated(MeetingCreatedEvent $event): void
    {
        $this->updateSheetIndexation($event->getMeeting()->getSheets());
    }

    /**
     * Update "from" and "to" sheets of the meeting request
     *
     * @param Request $request
     */
    private function updateSheetsIndexationFromRequest(Request $request): void
    {
        $this->updateSheetIndexation(
            [
                $request->getFromSheet(),
                $request->getToSheet(),
            ]
        );
    }

    /**
     * @param Sheet[] $sheets
     */
    private function updateSheetIndexation(array $sheets): void
    {
        $this->sheetIndexer->updateSheets($sheets);
    }
}
