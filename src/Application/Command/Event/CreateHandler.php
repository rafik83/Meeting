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
     * @var Generator
     */
    private $guidelinesGenerator;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * @param AdminRepositoryInterface $adminRepository,
     * @param EventRepositoryInterface $eventRepository
     * @param Generator                $guidelinesGenerator
     * @param FileStorageInterface     $fileStorage
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        EventRepositoryInterface $eventRepository,
        Generator $guidelinesGenerator,
        FileStorageInterface $fileStorage
    ) {
        $this->adminRepository     = $adminRepository;
        $this->eventRepository     = $eventRepository;
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
            $create->domain
        );

        $event->getConfiguration()->setColors($create->leftColor, $create->rightColor, $create->textColor);

        if (null !== $create->logo) {
            $event->setLogo($this->fileStorage->upload($create->logo));
        }

        try {
            $event->setAssetPath($this->guidelinesGenerator->generate($event));
        } catch (GuidelineAssetBuildFailedException $exception) {
            throw new GuidelineAssetBuildFailedException($exception->getMessage());
        }

        foreach ($event->getLocales() as $locale) {
            if (!$event->getTranslations()->get($locale)) {
                $event->getTranslations()->set($locale, new EventTranslation($event, $locale, ''));
            }
        }

        $this->eventRepository->add($event);

        if ($create->admin->isOrganizer()) {
            $create->admin->addEvent($event);
            $this->adminRepository->set($create->admin);
        }
    }
}
