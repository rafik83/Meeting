<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class CreateHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var ContentRepositoryInterface
     */
    private $contentRepository;

    /**
     * @var Generator
     */
    private $guidelinesGenerator;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * @param AdminRepositoryInterface   $adminRepository
     * @param EventRepositoryInterface   $eventRepository
     * @param ContentRepositoryInterface $contentRepository
     * @param Generator                  $guidelinesGenerator
     * @param FileStorageInterface       $fileStorage
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        EventRepositoryInterface $eventRepository,
        ContentRepositoryInterface $contentRepository,
        Generator $guidelinesGenerator,
        FileStorageInterface $fileStorage
    ) {
        $this->adminRepository     = $adminRepository;
        $this->eventRepository     = $eventRepository;
        $this->contentRepository   = $contentRepository;
        $this->guidelinesGenerator = $guidelinesGenerator;
        $this->fileStorage         = $fileStorage;
    }

    public function handle(Create $create)
    {
        if (null !== $this->eventRepository->getEventByDomain($create->domain)) {
            throw new DomainAlreadyUsedException(sprintf('Given domain %s', $create->domain));
        }

        $event = new Event(
            $create->title,
            $create->fallback,
            $create->locales,
            $create->mode,
            $create->vat,
            $create->country,
            $create->currency,
            $create->timeZone,
            $create->domain,
            $create->organiserName
        );

        $event->getConfiguration()->setColors($create->leftColor, $create->rightColor, $create->textColor);

        if (null !== $create->logo) {
            $logoExtension = $this->fileStorage->getExtension($create->logo);
            $logoPath      = $this->fileStorage->upload($create->logo);
            $event->setLogo($logoPath, $logoExtension);
        }

        foreach ($event->getLocales() as $locale) {
            if (!$event->getTranslations()->get($locale)) {
                $event->getTranslations()->set($locale, new EventTranslation($event, $locale, ''));
            }
        }

        $event->setAssetPath('');
        $this->eventRepository->add($event);

        try {
            $event->setAssetPath($this->guidelinesGenerator->generate($event));
            $this->eventRepository->set($event);
        } catch (GuidelineAssetBuildFailedException $exception) {
            throw new GuidelineAssetBuildFailedException($exception->getMessage());
        }

        if ($create->admin->isOrganizer()) {
            $create->admin->addEvent($event);
            $this->adminRepository->set($create->admin);
        }

        $this->generateContent($event);
    }

    /**
     * @param Event $event
     */
    private function generateContent(Event $event)
    {
        $termsOfSale = new Event\Content($event, Event\Content::TYPE_TERMS_OF_SALE);
        $this->contentRepository->add($termsOfSale);
    }
}
