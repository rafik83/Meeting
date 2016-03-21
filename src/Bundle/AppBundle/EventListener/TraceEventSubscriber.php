<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;

use DateTimeInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
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
            $event->getAuthor(),
            $event->getDate(),
            ''
        );
    }

    /**
     * @param TraceableInterface $traceable
     * @param string             $action
     * @param AbstractUser       $user
     * @param DateTimeInterface  $date
     * @param string             $comment
     */
    private function addTrace(TraceableInterface $traceable, $action, AbstractUser $user, DateTimeInterface $date, $comment)
    {
        $trace = new Trace(
            $traceable,
            $action,
            $user,
            $date,
            $comment
        );

        $this->traceRepository->add($trace);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_ACCEPTED => 'onSheetAccepted',
        ];
    }
}
