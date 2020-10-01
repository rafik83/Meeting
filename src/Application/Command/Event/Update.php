<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model;

class Update extends AbstractEvent
{
    /**
     * @var Model\Event
     */
    public $event;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var null|string
     */
    public $analyticsCode = null;

    /** @var bool */
    public $displayParticipantNameOnPlanning;

    /** @var bool */
    public $displayParticipantPositionOnPlanning;

    /** @var bool */
    public $visio;

    /** @var bool */
    public $googleLoginEnabled;

    /** @var bool */
    public $linkedinLoginEnabled;

    /** @var bool */
    public $accessControlEnabled;

    /** @var bool */
    public $showCheckinStatus;

    /**
     * @param Model\Event $event
     */
    public function __construct(Model\Event $event)
    {
        $this->event = $event;
        $this->title = $event->getTitle();
        $this->locales = $event->getLocales();
        $this->fallback = $event->getFallback();
        $this->translations = [];
        $this->mode = $event->getMode();
        $this->domain = $event->getDomain();
        $this->timeZone = $event->getTimeZone();
        $this->country = $event->getCountry();
        $this->vat = $event->getVat();
        $this->currency = $event->getCurrency();
        $this->organiserName = $event->getOrganiserName();
        $this->emailTeam = $event->getEmailTeam();
        $this->invoicePrefix = $event->getInvoicePrefix();
        $this->analyticsCode = $event->getConfiguration()->getAnalyticsCode();
        $this->visible = $event->isVisible();
        $this->welcomeEnabled = $event->isWelcomeEnabled();
        $this->disabledEmailChanging = $event->isDisabledEmailChanging();
        $this->disabledPasswordChanging = $event->isDisabledPasswordChanging();
        $this->displayParticipantPositionOnPlanning = $event->getConfiguration()->displayParticipantPositionOnPlanning();
        $this->displayParticipantNameOnPlanning = $event->getConfiguration()->displayParticipantNameOnPlanning();
        $this->visio = $event->getConfiguration()->isVisio();
        $this->googleLoginEnabled = $event->isGoogleLoginEnabled();
        $this->linkedinLoginEnabled = $event->isLinkedinLoginEnabled();
        $this->accessControlEnabled = $event->isAccessControlEnabled();
        $this->showCheckinStatus = $event->showCheckinStatus();
        $this->autoArchiveWebinar = $event->getAutoArchiveWebinar();

        foreach ($event->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'description' => $translation->getDescription(),
            ];
        }
    }

    /**
     * @return bool
     */
    public function isLocalesUpdated(): bool
    {
        return $this->locales !== $this->event->getLocales()
            || $this->fallback !== $this->event->getFallback()
        ;
    }
}
