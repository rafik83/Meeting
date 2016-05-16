<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

use Proximum\Vimeet\Domain\Model\EventInterface;

class EventView implements EventInterface
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
     * @var string
     */
    public $fallback;

    /**
     * @var array
     */
    public $locales;

    /**
     * @var string
     */
    public $timeZone;

    /**
     * @var string
     */
    public $asset;

    /**
     * EventView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $description
     * @param string $locale
     * @param string $fallback
     * @param array  $locales
     * @param string $timeZone
     * @param string $asset
     */
    public function __construct($id, $title, $description, $locale, $fallback, array $locales, $timeZone, $asset)
    {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
        $this->locale      = $locale;
        $this->fallback    = $fallback;
        $this->locales     = $locales;
        $this->timeZone    = $timeZone;
        $this->asset       = $asset;
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
