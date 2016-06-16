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
use Proximum\Vimeet\Domain\Model\Event;
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
        $colorUpdated = $update->isColorsUpdated();

        $event = $update->event;
        $event->update(
            $update->title,
            $update->locales,
            $update->fallback,
            $update->mode,
            $update->vat,
            $update->country,
            $update->currency
        );
        $event->getConfiguration()->setColors($update->leftColor, $update->rightColor, $update->textColor);

        $this->updateTranslatons($update);

        if ($colorUpdated) {
            $this->buildAssets($event);
        }

        $this->eventRepository->set($event);
    }

    /**
     * @param Update $update
     */
    private function updateTranslatons(Update $update)
    {
        // Create missing translation
        foreach ($update->event->getLocales() as $locale) {
            if (!$update->event->getTranslations()->get($locale)) {
                $update->event->getTranslations()->set($locale, new EventTranslation($update->event, $locale, ''));
            }
        }

        // Update translations
        foreach ($update->event->getTranslations() as $translation) {
            if (isset($update->translations[$translation->getLocale()])) {
                $translation->update($update->translations[$translation->getLocale()]['description']);
            }

            // Remove deleted translations
            if (!$update->event->hasLocale($translation->getLocale())) {
                $update->event->getTranslations()->removeElement($translation);
            }
        }
    }

    /**
     * @param Event $event
     *
     * @throws GuidelineAssetBuildFailedException
     */
    private function buildAssets(Event $event)
    {
        try {
            $event->setAssetPath($this->guidelinesGenerator->generate($event));
        } catch (GuidelineAssetBuildFailedException $exception) {
            throw new GuidelineAssetBuildFailedException($exception->getMessage());
        }
    }
}
