<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use DateTimeInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Group\Sheet\SheetCreatedByManagerEvent;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
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
    /**
     * @var TraceRepositoryInterface
     */
    private $traceRepository;

    /**
     * @param TraceRepositoryInterface $traceRepository
     */
    public function __construct(TraceRepositoryInterface $traceRepository)
    {
        $this->traceRepository = $traceRepository;
    }

    /**
     * @param SheetAcceptedEvent $event
     */
    public function onSheetAccepted(SheetAcceptedEvent $event)
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
    public function onSheetCatalog(SheetCatalogEvent $event)
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
    public function onSheetEnableDisable(SheetEnableDisableEvent $event)
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
    public function onSheetPending(SheetPendingEvent $event)
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
    public function onSheetValidationDraft(SheetDraftEvent $event)
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
    public function onSheetValidationValidate(SheetValidationValidateEvent $event)
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
    public function onSheetValidated(SheetValidatedEvent $event)
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
    public function onSheetChangedType(SheetChangedTypeEvent $event)
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
    public function onParticipantImported(ParticipantImportedEvent $event)
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
    public function onSheetCreateByGroupManager(SheetCreatedByManagerEvent $event)
    {
        $this->addTrace(
            $event->sheet,
            Trace::SHEET_CREATED_BY_GROUP_MANAGER,
            $event->date,
            '',
            $event->sheet->getGroup()->getManager()
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
    ) {
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
        ];
    }
}
