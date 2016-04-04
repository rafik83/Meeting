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
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
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
     * @param TraceableInterface $traceable
     * @param string             $action
     * @param DateTimeInterface  $date
     * @param string             $comment
     * @param AbstractUser|null  $user
     */
    private function addTrace(TraceableInterface $traceable, $action, DateTimeInterface $date, $comment, AbstractUser $user = null)
    {
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
            Events::SHEET_ACCEPTED  => 'onSheetAccepted',
            Events::SHEET_VALIDATED => 'onSheetValidated',
        ];
    }
}
