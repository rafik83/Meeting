<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
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

    /**
     * @param Model\Event $event
     */
    public function __construct(Model\Event $event)
    {
        $this->event         = $event;
        $this->title         = $event->getTitle();
        $this->locales       = $event->getLocales();
        $this->fallback      = $event->getFallback();
        $this->translations  = [];
        $this->mode          = $event->getMode();
        $this->domain        = $event->getDomain();
        $this->timeZone      = $event->getTimeZone();
        $this->country       = $event->getCountry();
        $this->vat           = $event->getVat();
        $this->currency      = $event->getCurrency();
        $this->leftColor     = $event->getConfiguration()->getLeftColor();
        $this->rightColor    = $event->getConfiguration()->getRightColor();
        $this->textColor     = $event->getConfiguration()->getTextColor();
        $this->organiserName = $event->getOrganiserName();
        $this->emailTeam     = $event->getEmailTeam();
        $this->invoicePrefix = $event->getInvoicePrefix();
        $this->analyticsCode = $event->getConfiguration()->getAnalyticsCode();
        $this->visible       = $event->isVisible();
        $this->backgroundColor = $event->getConfiguration()->getBackgroundColor();

        foreach ($event->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'description' => $translation->getDescription(),
            ];
        }
    }

    /**
     * @return bool
     */
    public function isColorsUpdated()
    {
        return
            $this->leftColor  !== $this->event->getConfiguration()->getLeftColor()  ||
            $this->rightColor !== $this->event->getConfiguration()->getRightColor() ||
            $this->textColor  !== $this->event->getConfiguration()->getTextColor();
    }

    /**
     * @return bool
     */
    public function isLocalesUpdated()
    {
        return
            $this->locales !== $this->event->getLocales() ||
            $this->fallback !== $this->event->getFallback();
    }
}
