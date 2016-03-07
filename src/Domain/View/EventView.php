<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class EventView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $locales;

    /**
     * @var string
     */
    public $timeZone;

    /**
     * @param int    $id
     * @param string $title
     * @param string $description
     * @param string $locale
     * @param array  $locales
     * @param string $timeZone
     */
    public function __construct($id, $title, $description, $locale, array $locales, $timeZone)
    {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
        $this->locale      = $locale;
        $this->locales     = $locales;
        $this->timeZone    = $timeZone;
    }

    /**
     * @param $locale
     *
     * @return bool
     */
    public function hasLocale($locale)
    {
        return in_array($locale, $this->locales);
    }
}
