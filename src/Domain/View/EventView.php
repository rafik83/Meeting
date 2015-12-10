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
     * @param int    $id
     * @param string $title
     * @param string $description
     * @param string $locale
     * @param array  $locales
     */
    public function __construct($id, $title, $description, $locale, array $locales)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->locale = $locale;
        $this->locales = $locales;
    }
}
