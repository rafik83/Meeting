<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Event\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class CreateHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var ContentRepositoryInterface */
    private $contentRepository;

    /** @var Generator */
    private $guidelinesGenerator;

    /** @var Duplicator */
    private $duplicator;

    /**
     * @param AdminRepositoryInterface   $adminRepository
     * @param EventRepositoryInterface   $eventRepository
     * @param ContentRepositoryInterface $contentRepository
     * @param Generator                  $guidelinesGenerator
     * @param Duplicator                 $duplicator
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        EventRepositoryInterface $eventRepository,
        ContentRepositoryInterface $contentRepository,
        Generator $guidelinesGenerator,
        Duplicator $duplicator
    ) {
        $this->adminRepository = $adminRepository;
        $this->eventRepository = $eventRepository;
        $this->contentRepository = $contentRepository;
        $this->guidelinesGenerator = $guidelinesGenerator;
        $this->duplicator = $duplicator;
    }

    /**
     * @param Create $create
     *
     * @throws DomainAlreadyUsedException
     * @throws GuidelineAssetBuildFailedException
     *
     * @return Event
     */
    public function handle(Create $create): Event
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
            $create->organiserName,
            $create->emailTeam,
            $create->invoicePrefix,
            $create->visible,
            $create->duplicatedFrom,
            $create->welcomeEnabled,
            $create->disabledEmailChanging,
            $create->disabledPasswordChanging,
            $create->autoArchiveWebinar
        );

        $event->getConfiguration()->setVisio($create->visio);

        if (null !== $event->getDuplicatedFrom()) {
            $event->getConfiguration()->setColors(
                $event->getDuplicatedFrom()->getConfiguration()->getLeftColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getRightColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getTextColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getHeaderLeftColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getHeaderRightColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getBackgroundColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getHeaderButtonLeftColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getHeaderButtonRightColor(),
                $event->getDuplicatedFrom()->getConfiguration()->getHeaderButtonTextColor()
            );
        }

        foreach ($event->getLocales() as $locale) {
            if (!$event->getTranslations()->get($locale)) {
                $event->getTranslations()->set($locale, new EventTranslation($event, $locale, ''));
            }
        }

        $event->setAssetPath('');
        $this->eventRepository->add($event);

        $event->setAssetPath($this->guidelinesGenerator->generate($event));
        $this->eventRepository->set($event);

        if ($create->admin->isOrganizer()) {
            $create->admin->addEvent($event);
            $this->adminRepository->set($create->admin);
        }

        $this->generateContent($event);

        if (null !== $create->duplicatedFrom) {
            $this->duplicator->duplicate($event);
        }

        return $event;
    }

    /**
     * @param Event $event
     */
    private function generateContent(Event $event): void
    {
        $termsOfSale = new Event\Content($event, Event\Content::TYPE_TERMS_OF_SALE);
        $this->contentRepository->add($termsOfSale);
    }
}
