<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\AssignedEvent;
use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class AffectHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * AffectHandler constructor.
     *
     * @param TipRepositoryInterface          $tipRepository
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     * @param \DateTimeInterface              $dateTime
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->tipRepository  = $tipRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Affect $affect
     *
     * @throws TipNotFoundException
     */
    public function handle(Affect $affect)
    {
        $globalTip = $affect->tip;

        $tip = new Tip(
            $globalTip->getTitle(),
            $affect->event,
            $globalTip->isOnMeetingManagement(),
            $globalTip->isOnCatalog(),
            $globalTip->isOnPrintPlanning(),
            $globalTip->isOnSheet(),
            $globalTip->isOnAgenda(),
            $globalTip->isOnPackage(),
            $globalTip->isOnContacts(),
            $globalTip->isOnProgram(),
            $globalTip->isOnConfirmationPhone(),
            $globalTip->isOnNetworking(),
            $this->dateTime
        );

        foreach ($affect->event->getLocales() as $locale) {
            $tip->translate(
                $locale,
                $globalTip->getTranslationTitle($locale),
                $globalTip->getTranslationContent($locale),
                $this->dateTime
            );
        }

        foreach ($affect->types as $type) {
            $tip->setType($type);
        }

        $this->tipRepository->add($tip);

        $this->delayedEventDispatcher->dispatch(Events::TIP_ASSIGNED, new AssignedEvent($affect->event, $tip));
    }
}
