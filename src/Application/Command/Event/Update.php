<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class Update
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $locales;

    /**
     * @var string
     */
    public $fallback;

    /**
     * @var string
     */
    public $country;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var float
     */
    public $vat;

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * @var string
     */
    public $textColor;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event        = $event;
        $this->title        = $event->getTitle();
        $this->locales      = $event->getLocales();
        $this->fallback     = $event->getFallback();
        $this->translations = [];
        $this->mode         = $event->getMode();
        $this->country      = $event->getCountry();
        $this->vat          = $event->getVat();
        $this->leftColor    = $event->getConfiguration()->getLeftColor();
        $this->rightColor   = $event->getConfiguration()->getRightColor();
        $this->textColor    = $event->getConfiguration()->getTextColor();

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
            $this->leftColor  !== $this->event->getConfiguration()->getLeftColor() ||
            $this->rightColor !== $this->event->getConfiguration()->getRightColor() ||
            $this->textColor  !== $this->event->getConfiguration()->getTextColor();
    }
}
