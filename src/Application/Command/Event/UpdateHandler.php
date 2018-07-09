<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImage;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImageHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Event\Event\LocaleChangedEvent;
use Proximum\Vimeet\Application\Event\Event\VisioUpdatedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /** @var RemoveImageHandler */
    private $removeImageHandler;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param Generator                $guidelinesGenerator
     * @param FileStorageInterface     $fileStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param RemoveImageHandler       $removeImageHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        Generator $guidelinesGenerator,
        FileStorageInterface $fileStorage,
        EventDispatcherInterface $eventDispatcher,
        RemoveImageHandler $removeImageHandler
    ) {
        $this->eventRepository     = $eventRepository;
        $this->guidelinesGenerator = $guidelinesGenerator;
        $this->fileStorage         = $fileStorage;
        $this->eventDispatcher     = $eventDispatcher;
        $this->removeImageHandler = $removeImageHandler;
    }

    /**
     * @param Update $update
     *
     * @throws DomainAlreadyUsedException
     * @throws GuidelineAssetBuildFailedException
     */
    public function handle(Update $update): void
    {
        $colorUpdated     = $update->isColorsUpdated();
        $isLocalesUpdated = $update->isLocalesUpdated();
        $backgroundUpdated = $update->isBackgroundUpdated();

        if ($update->domain !== $update->event->getDomain()) {
            $foundEvent = $this->eventRepository->getEventByDomain($update->domain);

            if (null !== $foundEvent && $update->event !== $foundEvent) {
                throw new DomainAlreadyUsedException(sprintf('Given domain %s', $update->domain));
            }
        }

        $event = $update->event;
        $event->update(
            $update->title,
            $update->locales,
            $update->fallback,
            $update->mode,
            $update->vat,
            $update->country,
            $update->currency,
            $update->timeZone,
            $update->domain,
            $update->organiserName,
            $update->emailTeam,
            $update->invoicePrefix,
            $update->visible,
            $update->welcomeEnabled,
            $update->disabledEmailChanging,
            $update->disabledPasswordChanging
        );
        $event->getConfiguration()->setColors(
            $update->leftColor,
            $update->rightColor,
            $update->textColor,
            $update->backgroundColor
        );

        if ($event->getConfiguration()->isVisio() !== $update->visio) {
            $this->eventDispatcher->dispatch(
                Events::EVENT_VISIO_UPDATED,
                new VisioUpdatedEvent($update)
            );
        }

        $event->getConfiguration()->setVisio($update->visio);

        $event->getConfiguration()->setParticipantInfoToDisplayOnPlanning(
            $update->displayParticipantNameOnPlanning,
            $update->displayParticipantPositionOnPlanning
        );

        $update->event->getConfiguration()->setAnalyticsCode($update->analyticsCode);

        if (null !== $update->logo) {
            $toRemove      = $event->getLogo();
            $logoExtension = $this->fileStorage->getExtension($update->logo);
            $logoPath      = $this->fileStorage->upload($update->logo);
            $event->setLogo($logoPath, $logoExtension);
            $this->fileStorage->remove($toRemove);
        }

        if (null !== $update->backgroundImage) {
            $backgroundImageToRemove  = $event->getConfiguration()->getBackgroundImage();
            $backGroundImagePath      = $this->fileStorage->upload($update->backgroundImage);
            $event->getConfiguration()->setBackgroundImage($backGroundImagePath);
            $this->fileStorage->remove($backgroundImageToRemove);
        }

        if ($update->isBackgroundImageToRemove) {
            $this->removeImageHandler->handle(new RemoveImage($event));
        }

        $this->updateTranslatons($update);

        if ($colorUpdated || $backgroundUpdated || $update->isBackgroundImageToRemove) {
            $this->buildAssets($event);
        }

        $this->eventRepository->set($event);

        if ($isLocalesUpdated) {
            $localeChangedEvent = new LocaleChangedEvent($event);
            $this->eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $localeChangedEvent);
        }
    }

    /**
     * @param Update $update
     */
    private function updateTranslatons(Update $update): void
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
    private function buildAssets(Event $event): void
    {
        try {
            $event->setAssetPath($this->guidelinesGenerator->generate($event));
        } catch (GuidelineAssetBuildFailedException $exception) {
            throw new GuidelineAssetBuildFailedException($exception->getMessage());
        }
    }
}
