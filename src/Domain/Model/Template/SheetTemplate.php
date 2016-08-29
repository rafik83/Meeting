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

class SheetTemplate extends AbstractTemplate
{
    /**
     * @var array
     */
    protected $preview;

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->event ? $this->event->getFallback() : $this->fallback;
    }

    /**
     * @param string            $title
     * @param array             $value
     * @param DateTimeInterface $createdAt
     *
     * @return SheetTemplate
     */
    public function duplicate($title, array $value, DateTimeInterface $createdAt)
    {
        return new $this($title, $value, $this->locales, $this->fallback, $createdAt);
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
}
