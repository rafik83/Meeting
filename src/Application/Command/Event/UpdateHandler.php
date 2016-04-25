<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UpdateHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var Generator
     */
    private $guidelinesGenerator;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param Generator                $guidelinesGenerator
     */
    public function __construct(EventRepositoryInterface $eventRepository, Generator $guidelinesGenerator)
    {
        $this->eventRepository     = $eventRepository;
        $this->guidelinesGenerator = $guidelinesGenerator;
    }

    /**
     * @param Update $update
     *
     * @throws GuidelineAssetBuildFailedException
     */
    public function handle(Update $update)
    {
        $leftColor    = $update->leftColor;
        $rightColor   = $update->rightColor;
        $textColor    = $update->textColor;
        $colorUpdated = false;

        $event = $update->event;
        $event->update($update->title, $update->locales, $update->fallback, $update->mode, $update->vat);

        if ($leftColor !== $event->getConfiguration()->getLeftColor()) {
            $event->getConfiguration()->setLeftColor($leftColor);
            $colorUpdated = true;
        }

        if ($rightColor !== $event->getConfiguration()->getRightColor()) {
            $event->getConfiguration()->setRightColor($rightColor);
            $colorUpdated = true;
        }

        if ($textColor !== $event->getConfiguration()->getTextColor()) {
            $event->getConfiguration()->setTextColor($textColor);
            $colorUpdated = true;
        }

        foreach ($event->getLocales() as $locale) {
            if (!$event->getTranslations()->get($locale)) {
                $event->getTranslations()->set($locale, new EventTranslation($event, $locale, ''));
            }
        }

        foreach ($event->getTranslations() as $translation) {
            if (isset($update->translations[$translation->getLocale()])) {
                $translation->update($update->translations[$translation->getLocale()]['description']);
            }

            if (!$event->hasLocale($translation->getLocale())) {
                $event->getTranslations()->removeElement($translation);
            }
        }

        if ($colorUpdated) {
            try {
                $assetPath = $this->guidelinesGenerator->generate($event);

                $event->setAssetPath($assetPath);
            } catch(GuidelineAssetBuildFailedException $ex) {
                throw new GuidelineAssetBuildFailedException($ex->getMessage());
            }
        }

        $this->eventRepository->set($event);
    }
}
