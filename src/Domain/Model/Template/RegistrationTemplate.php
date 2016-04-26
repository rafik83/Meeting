<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Template;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class RegistrationTemplate extends AbstractTemplate
{
    /**
     * RegistrationTemplate constructor.
     *
     * @param string            $title
     * @param array             $value
     * @param array             $locales
     * @param string            $fallback
     * @param DateTimeInterface $createdAt
     */
    public function __construct($title, array $value, array $locales, $fallback, DateTimeInterface $createdAt)
    {
        $this->title     = $title;
        $this->value     = $value;
        $this->fallback  = $fallback;
        $this->createdAt = $createdAt;

        foreach ($locales as $locale) {
            $this->addLocale($locale);
        }

        if (!$this->hasLocale($fallback)) {
            throw new \InvalidArgumentException('Default locale should be in the template locales.');
        }
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get locales available for the current event if set, else get all locales.
     *
     * @return array
     */
    public function getEnabledLocales()
    {
        return $this->event ? array_filter($this->locales, function ($locale) {
            return $this->event->hasLocale($locale);
        }) : $this->locales;
    }

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->event ? $this->event->getFallback() : $this->fallback;
    }

    /**
     * @param string $locale
     *
     * @return RegistrationTemplate
     */
    public function addLocale($locale)
    {
        if (!$this->hasLocale($locale) && $locale !== null) {
            $this->locales[] = $locale;
            $this->fixValue([$locale]);
        }

        return $this;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        // Add event locales
        foreach ($event->getLocales() as $locale) {
            $this->addLocale($locale);
        }
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return RegistrationTemplate
     */
    public function setValue($value)
    {
        $this->value = $value;

        $this->fixValue($this->locales);

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string            $title
     * @param DateTimeInterface $createdAt
     *
     * @return RegistrationTemplate
     */
    public function duplicate($title, DateTimeInterface $createdAt)
    {
        return new $this($title, $this->value, $createdAt, $this->locales);
    }

    /**
     * Get locales
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param string $title
     * @param string $fallback
     *
     * @return RegistrationTemplate
     */
    public function update($title, $fallback)
    {
        if (!$this->hasLocale($fallback)) {
            throw new \InvalidArgumentException('Default locale should be in the template locales.');
        }

        $this->title    = $title;
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * Consolidate value for each locales
     *
     * @param array $locales
     */
    private function fixValue(array $locales)
    {
        foreach ($locales as $locale) {
            $this->value = self::createLocale($this->value, $locale);
        }
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasLocale($locale)
    {
        return in_array($locale, $this->locales);
    }
}
