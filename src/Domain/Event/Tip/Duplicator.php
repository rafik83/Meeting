<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Tip;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class Duplicator
{
    /**
     * @var TipRepositoryInterface
     */
    private $tipRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param TipRepositoryInterface $tipRepository
     * @param \DateTimeInterface     $dateTime
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->tipRepository = $tipRepository;
        $this->dateTime      = $dateTime;
    }

    /**
     * @param Event $event
     * @param array $duplicationHelper
     */
    public function duplicate(Event $event, array $duplicationHelper)
    {
        $tips = $this->tipRepository->getByEvent($event->getDuplicatedFrom());

        foreach ($tips as $tip) {
            $newTip = new Tip(
                $tip->getTitle(),
                $tip->isOnMeetingManagement(),
                $tip->isOnCatalog(),
                $tip->isOnPrintPlanning(),
                $tip->isOnSheet(),
                $tip->isOnAgenda(),
                $tip->isOnProgram(),
                $tip->isOnConfirmationPhone(),
                $this->dateTime
            );

            foreach ($tip->getTypes() as $type) {
                $newTip->addType($duplicationHelper['type'][$type->getId()]);
            }

            foreach ($event->getLocales() as $locale) {
                $newTip->translate(
                    $locale,
                    $tip->getTranslationTitle($locale),
                    $tip->getTranslationContent($locale),
                    $this->dateTime
                );
            }

            $this->tipRepository->add($newTip);
        }
    }
}
