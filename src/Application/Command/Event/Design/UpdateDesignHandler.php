<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Design;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImage;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImageHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator as GuidelineGenerator;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateDesignHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var RemoveImageHandler */
    private $removeImageHandler;

    /** @var GuidelineGenerator */
    private $guidelineGenerator;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        FileStorageInterface $fileStorage,
        RemoveImageHandler $removeImageHandler,
        GuidelineGenerator $guidelineGenerator
    ) {
        $this->eventRepository = $eventRepository;
        $this->fileStorage = $fileStorage;
        $this->removeImageHandler = $removeImageHandler;
        $this->guidelineGenerator = $guidelineGenerator;
    }

    /**
     * @param UpdateDesign $command
     *
     * @throws GuidelineAssetBuildFailedException
     */
    public function handle(UpdateDesign $command): void
    {
        $colorUpdated = $command->isColorsUpdated();

        $event = $command->event;

        foreach ($command->localizedImages as $locale => $localizedImage) {
            $logo = $event->getLocalizedLogo($locale);
            $logoExtension = $event->getLocalizedLogoExtension($locale);
            $mobileLogo = $event->getLocalizedMobileLogo($locale);
            $mobileLogoExtension = $event->getLocalizedMobileLogoExtension($locale);
            $notificationImage = $event->getLocalizedNotificationImage($locale);
            $notificationImageExtension = $event->getLocalizedNotificationImageExtension($locale);

            if ($localizedImage['logo'] instanceof UploadedFile) {
                $logoToRemove = $event->getLocalizedLogo($locale);
                $this->fileStorage->remove($logoToRemove);

                $logoExtension = $this->fileStorage->getExtension($localizedImage['logo']);
                $logo = $this->fileStorage->upload($localizedImage['logo']);
            }

            if ($localizedImage['mobileLogo'] instanceof UploadedFile) {
                $logoToRemove = $event->getLocalizedMobileLogo($locale);
                $this->fileStorage->remove($logoToRemove);

                $mobileLogoExtension = $this->fileStorage->getExtension($localizedImage['mobileLogo']);
                $mobileLogo = $this->fileStorage->upload($localizedImage['mobileLogo']);
            }

            if ($localizedImage['notificationImage'] instanceof UploadedFile) {
                $logoToRemove = $event->getLocalizedNotificationImage($locale);
                $this->fileStorage->remove($logoToRemove);

                $notificationImageExtension = $this->fileStorage->getExtension($localizedImage['notificationImage']);
                $notificationImage = $this->fileStorage->upload($localizedImage['notificationImage']);
            }

            $event->updateLocalizedLogos(
                $locale,
                $logo,
                $logoExtension,
                $mobileLogo,
                $mobileLogoExtension,
                $notificationImage,
                $notificationImageExtension
            );
        }

        if ($command->removeBackgroundImage) {
            $this->removeImageHandler->handle(new RemoveImage($event));
        }

        if ($command->backgroundImage instanceof UploadedFile) {
            $backgroundImageToRemove = $event->getConfiguration()->getBackgroundImage();
            $this->fileStorage->remove($backgroundImageToRemove);

            $backGroundImagePath = $this->fileStorage->upload($command->backgroundImage);
            $event->getConfiguration()->setBackgroundImage($backGroundImagePath);
        }

        $event->getConfiguration()->setColors(
            $command->leftColor,
            $command->rightColor,
            $command->textColor,
            $command->headerLeftColor,
            $command->headerRightColor,
            $command->backgroundColor,
            $command->headerButtonLeftColor,
            $command->headerButtonRightColor,
            $command->headerButtonTextColor
        );

        if (true === $colorUpdated || true === $command->backgroundImageChanged()) {
            $this->buildAssets($event);
        }

        $this->eventRepository->set($event);
    }

    /**
     * @param Event $event
     *
     * @throws GuidelineAssetBuildFailedException
     */
    private function buildAssets(Event $event): void
    {
        $event->setAssetPath($this->guidelineGenerator->generate($event));
    }
}
