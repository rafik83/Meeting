<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use DateTimeInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Group\Sheet\SheetCreatedByManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Event\Sheet\CommercialStatusChanged;
use Proximum\Vimeet\Application\Event\Sheet\Order\OrdersCancelledEvent;
use Proximum\Vimeet\Application\Event\Sheet\OwnerChangedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetCatalogEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetDraftEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetPendingEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidationValidateEvent;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\TraceableInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TraceEventSubscriber implements EventSubscriberInterface
{
    /** @var TraceRepositoryInterface */
    private $traceRepository;

    /** @var DateTimeInterface */
    private $dateTime;

    /**
     * @param TraceRepositoryInterface $traceRepository
     * @param DateTimeInterface        $dateTime
     */
    public function __construct(
        TraceRepositoryInterface $traceRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->traceRepository = $traceRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param CommercialStatusChanged $event
     */
    public function onSheetCommercialStatusChanged(CommercialStatusChanged $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            Trace::SET_COMMERCIAL_STATUS,
            $event->getDate(),
            $event->getSheet()->getCommercialStatus(),
            $event->getAuthor()
        );
    }

    /**
     * @param SheetAcceptedEvent $event
     */
    public function onSheetAccepted(SheetAcceptedEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            Trace::ACCEPT,
            $event->getDate(),
            '',
            $event->getAuthor()
        );
    }

    /**
     * @param SheetCatalogEvent $event
     */
    public function onSheetCatalog(SheetCatalogEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            ($event->getState()) ? Trace::ENABLE_CATALOG : Trace::DISABLE_CATALOG,
            $event->getDate(),
            '',
            $event->getAuthor()
        );
    }

    /**
     * @param SheetEnableDisableEvent $event
     */
    public function onSheetEnableDisable(SheetEnableDisableEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            ($event->getState()) ? Trace::ENABLE : Trace::DISABLE,
            $event->getDate(),
            '',
            $event->getAuthor()
        );
    }

    /**
     * @param SheetPendingEvent $event
     */
    public function onSheetPending(SheetPendingEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            Trace::PENDING,
            $event->getDate(),
            '',
            $event->getAuthor()
        );
    }

    /**
     * @param SheetDraftEvent $event
     */
    public function onSheetValidationDraft(SheetDraftEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            Trace::VALIDATION_DRAFT,
            $event->getDate(),
            '',
            $event->getAuthor()
        );
    }

    /**
     * @param SheetValidationValidateEvent $event
     */
    public function onSheetValidationValidate(SheetValidationValidateEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            Trace::VALIDATION_VALIDATE,
            $event->getDate(),
            '',
            $event->getAuthor()
        );
    }

    /**
     * @param SheetValidatedEvent $event
     */
    public function onSheetValidated(SheetValidatedEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            Trace::VALIDATE,
            $event->getDate(),
            $event->getComment(),
            $event->getAuthor()
        );
    }

    /**
     * @param SheetChangedTypeEvent $event
     */
    public function onSheetChangedType(SheetChangedTypeEvent $event): void
    {
        $this->addTrace(
            $event->getSheet(),
            Trace::CHANGED_TYPE,
            $event->getDate(),
            $event->getComment(),
            $event->getAuthor()
        );
    }

    /**
     * @param ParticipantImportedEvent $event
     */
    public function onParticipantImported(ParticipantImportedEvent $event): void
    {
        $this->addTrace(
            $event->getEvent(),
            Trace::PARTICIPANT_IMPORTED,
            $event->getDate(),
            '',
            $event->getAdmin()
        );
    }

    /**
     * @param SheetCreatedByManagerEvent $event
     */
    public function onSheetCreateByGroupManager(SheetCreatedByManagerEvent $event): void
    {
        $this->addTrace(
            $event->sheet,
            Trace::SHEET_CREATED_BY_GROUP_MANAGER,
            $event->date,
            '',
            $event->sheet->getGroup()->getManager()
        );
    }

    public function onOrdersCancelled(OrdersCancelledEvent $event): void
    {
        $this->addTrace(
            $event->sheet,
            Trace::ORDERS_CANCELLED,
            $this->dateTime,
            '',
            $event->admin
        );
    }

    public function onSheetOwnerChanged(OwnerChangedEvent $event): void
    {
        $this->addTrace(
            $event->sheet,
            Trace::SHEET_OWNER_CHANGED,
            $this->dateTime,
            $event->comment,
            $event->admin
        );
    }

    /**
     * @param TraceableInterface $traceable
     * @param string             $action
     * @param DateTimeInterface  $date
     * @param string             $comment
     * @param null|AbstractUser  $user
     */
    private function addTrace(
        TraceableInterface $traceable,
        $action,
        DateTimeInterface $date,
        $comment,
        AbstractUser $user = null
    ): void {
        $trace = new Trace(
            $traceable,
            $action,
            $date,
            $comment,
            $user
        );

        $this->traceRepository->add($trace);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_ACCEPTED                => 'onSheetAccepted',
            Events::SHEET_VALIDATED               => 'onSheetValidated',
            Events::SHEET_PENDING                 => 'onSheetPending',
            Events::SHEET_ENABLE_DISABLE          => 'onSheetEnableDisable',
            Events::SHEET_CATALOG                 => 'onSheetCatalog',
            Events::SHEET_CHANGED_TYPE            => 'onSheetChangedType',
            Events::SHEET_VALIDATION_DRAFT        => 'onSheetValidationDraft',
            Events::SHEET_VALIDATION_VALIDATE     => 'onSheetValidationValidate',
            Events::PARTICIPANT_IMPORTED          => 'onParticipantImported',
            Events::SHEET_CREATE_BY_GROUP_MANAGER => 'onSheetCreateByGroupManager',
            Events::SHEET_SET_COMMERCIAL_STATUS   => 'onSheetCommercialStatusChanged',
            Events::SHEET_ORDERS_CANCELLED        => 'onOrdersCancelled',
            Events::SHEET_OWNER_CHANGED           => 'onSheetOwnerChanged',
        ];
    }
}
