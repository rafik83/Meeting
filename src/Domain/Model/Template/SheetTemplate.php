<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Template;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;

class SheetTemplate extends AbstractTemplate
{
    /**
     * @var array
     */
    protected $preview;

    /**
     * @var array
     */
    protected $printValue;

    /**
     * SheetTemplate constructor.
     *
     * @param string            $title
     * @param array             $value
     * @param array             $locales
     * @param string            $fallback
     * @param DateTimeInterface $createdAt
     * @param array             $preview
     * @param Event|null        $event
     */
    public function __construct(
        $title,
        array $value,
        array $locales,
        $fallback,
        \DateTimeInterface $createdAt,
        array $preview = [],
        Event $event = null
    ) {
        parent::__construct($title, $value, $locales, $fallback, $createdAt, $event);

        $this->preview = $preview;
    }

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->event ? $this->event->getFallback() : $this->fallback;
    }

    /**
     * @param string $title
     * @param string $fallback
     *
     * @return SheetTemplate
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
     * @return array
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param array $preview
     *
     * @return SheetTemplate
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getAvailableLocale($locale)
    {
        if (in_array($locale, $this->getLocales())) {
            return $locale;
        }

        return $this->getFallback();
    }

    /**
     * @return array
     */
    public function getPrintValue(): array
    {
        return $this->printValue;
    }

    /**
     * @param array $printValue
     */
    public function setPrintValue(array $printValue)
    {
        $this->printValue = $printValue;
    }
}
