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
use DateTimeInterface;

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
     * @param string            $title
     * @param DateTimeInterface $createdAt
     *
     * @return Template
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

    /**
     * @param $locale
     *
     * @return float
     */
    public function getRate($locale)
    {
        list ($set, $total) = self::countLocale($this->value, $locale, $this->getFallback());

        return $set / $total * 100;
    }

    /**
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     *
     * @return array
     */
    private static function countLocale($config, $locale, $fallback)
    {
        $set   = 0;
        $total = 0;

        $keys = ['label', 'help', 'placeholder'];

        if (!isset($config['component'])) {
            foreach ($config as $item) {
                list($s, $t) = self::countLocale($item, $locale, $fallback);
                $set   += $s;
                $total += $t;
            }

            return [$set, $total];
        }

        if ($config['component'] === 'block') {
            foreach ($config['config'] as $key => $column) {
                list($s, $t) = self::countLocale($column, $locale, $fallback);
                $set   += $s;
                $total += $t;
            }

            return [$set, $total];
        }

        if ($config['component'] === 'object') {
            foreach ($config['config'] as $key => $value) {
                if (in_array($key, $keys) || $config['type'] === 'text' && $key === 'content') {
                    if (!empty($value[$fallback])) {
                        if (!empty($value[$locale])) {
                            $set++;
                        }

                        $total++;
                    }
                }
            }

            return [$set, $total];
        }

        return [$set, $total];
    }
}
