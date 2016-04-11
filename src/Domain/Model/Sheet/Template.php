<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Template
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $value = [];

    /**
     * @var array
     */
    private $locales = [];

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Type[]
     */
    private $types;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * Template constructor.
     *
     * @param string             $title
     * @param array              $value
     * @param array              $locales
     * @param \DateTimeInterface $createdAt
     */
    public function __construct($title, array $value, array $locales, \DateTimeInterface $createdAt)
    {
        $this->title     = $title;
        $this->value     = $value;
        $this->createdAt = $createdAt;

        foreach ($locales as $locale) {
            $this->addLocale($locale);
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
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
     * @return Template
     */
    public function setValue($value)
    {
        $this->value = $value;

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
     * Get locales
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @return array
     */
    public function getEnableLocales()
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
        $locales = $this->getEnableLocales();

        return reset($locales);
    }

    /**
     * @param string $locale
     *
     * @return Template
     */
    public function addLocale($locale)
    {
        if (!$this->hasLocale($locale)) {
            $this->locales[] = $locale;
            $this->value     = self::createLocale($this->value, $locale);
        }

        return $this;
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

    /**
     * @param array  $config
     * @param string $locale
     *
     * @return array
     */
    private static function createLocale($config, $locale) {

        $keys = ['label', 'help', 'placeholder'];

        if (!isset($config['component'])) {
            return array_map(function ($item) use ($locale) { return self::createLocale($item, $locale); }, $config);
        }

        if ($config['component'] === 'block') {
            foreach ($config['config'] as $key => $column) {
                $config['config'][$key] = self::createLocale($column, $locale);
            }

            return $config;
        }

        if ($config['component'] === 'object') {
            foreach ($config['config'] as $key => $value) {
                if (in_array($key, $keys) || $config['type'] === 'text' && $key === 'content') {
                    $config['config'][$key] = array_merge([$locale => null], $config['config'][$key]);
                }
            }

            return $config;
        }

        return $config;
    }
}
